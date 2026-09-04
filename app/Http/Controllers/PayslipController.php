<?php

namespace App\Http\Controllers;

use App\Mail\PayslipMail;
use App\Models\Company;
use App\Models\Employee;
use App\Services\PayrollCalculator;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Throwable;

class PayslipController extends Controller
{
    public function index(Request $request, PayrollCalculator $calculator)
    {
        [$start, $end] = $this->period($request);
        $request->validate(['company_id' => ['nullable', 'exists:companies,id']]);
        $companies = Company::orderBy('name')->get();
        $employees = Employee::with('award')->when($request->filled('company_id'), fn ($q) => $q->whereHas('companies', fn ($q) => $q->whereKey($request->integer('company_id'))))->orderBy('name')->get();
        $payslips = $employees->map(fn ($employee) => $calculator->calculate($employee, $start, $end));
        return view('payslips.index', compact('payslips', 'companies', 'start', 'end'));
    }

    public function show(Request $request, Employee $employee, PayrollCalculator $calculator)
    {
        [$start, $end] = $this->period($request);
        return view('payslips.show', ['payslip' => $calculator->calculate($employee, $start, $end)]);
    }

    public function pdf(Request $request, Employee $employee, PayrollCalculator $calculator)
    {
        [$start, $end] = $this->period($request); $payslip = $calculator->calculate($employee, $start, $end);
        return Pdf::loadView('payslips.pdf', compact('payslip'))->download("payslip-{$employee->id}-{$start->toDateString()}.pdf");
    }

    public function email(Request $request, PayrollCalculator $calculator)
    {
        $data = $request->validate(['employee_ids' => ['required', 'array', 'min:1'], 'employee_ids.*' => ['integer', 'exists:employees,id'], 'start_date' => ['required', 'date'], 'end_date' => ['required', 'date', 'after_or_equal:start_date']]);
        $employees = Employee::whereIn('id', $data['employee_ids'])->get(); $sent = 0; $failed = 0; $missing = [];
        foreach ($employees as $employee) {
            if (! $employee->email) { $missing[] = $employee->name; continue; }
            $payslip = $calculator->calculate($employee, Carbon::parse($data['start_date']), Carbon::parse($data['end_date']));
            try {
                Mail::to($employee->email)->send(new PayslipMail($payslip)); $sent++;
            } catch (Throwable $exception) {
                report($exception); $failed++;
            }
        }
        $message = "{$sent} payslip".($sent === 1 ? '' : 's').' emailed.';
        if ($missing) $message .= ' Missing email: '.implode(', ', $missing).'.';
        if ($failed) $message .= " {$failed} failed to send; check the mail log.";
        return back()->with($sent ? 'success' : 'warning', $message);
    }

    private function period(Request $request): array
    {
        $request->validate(['start_date' => ['nullable', 'date'], 'end_date' => ['nullable', 'date', 'after_or_equal:start_date']]);
        $start = Carbon::parse($request->input('start_date', now()->startOfWeek()->toDateString()))->startOfDay();
        $end = Carbon::parse($request->input('end_date', now()->endOfWeek()->toDateString()))->endOfDay();
        return [$start, $end];
    }
}

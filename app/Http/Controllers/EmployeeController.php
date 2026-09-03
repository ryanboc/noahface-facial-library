<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeRequest;
use App\Models\Award;
use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['company_id' => ['nullable', 'integer', 'exists:companies,id']]);
        $search = trim((string) $request->query('search', ''));
        $perPage = (int) $request->query('per_page', 10);
        $companyId = $request->integer('company_id') ?: null;
        $companies = Company::orderBy('name')->get();

        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        // Eager load the award so we can display "Poultry Award" instead of "ID: 1".
        $employees = Employee::with(['award', 'companies'])
            ->when($companyId, fn ($query) => $query->whereHas('companies', fn ($query) => $query->whereKey($companyId)))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('noahface_id', 'like', "%{$search}%")
                        ->orWhere('employment_type', 'like', "%{$search}%")
                        ->orWhereHas('companies', fn ($query) => $query->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('award', fn ($query) => $query->where('name', 'like', "%{$search}%"));
                });
            })
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return view('employees.index', compact('employees', 'companies', 'companyId'));
    }

    public function create()
    {
        $awards = Award::orderBy('name')->get();
        $companies = Company::orderBy('name')->get();

        return view('employees.create', compact('awards', 'companies'));
    }

    public function store(StoreEmployeeRequest $request)
    {
        $employee = Employee::create($request->safe()->except(['account_role', 'company_ids']));
        $employee->companies()->sync($request->input('company_ids', []));

        return redirect()->route('employees.index')
            ->with('success', 'Employee linked successfully.');
    }

    public function edit(Employee $employee)
    {
        $awards = Award::orderBy('name')->get();
        $linkedAccount = User::where('email', $employee->email)->first();
        $companies = Company::orderBy('name')->get();

        $employee->load('companies');

        return view('employees.edit', compact('employee', 'awards', 'linkedAccount', 'companies'));
    }

    public function update(StoreEmployeeRequest $request, Employee $employee)
    {
        $employee->update($request->safe()->except(['account_role', 'company_ids']));
        $employee->companies()->sync($request->input('company_ids', []));

        if ($request->filled('account_role')) {
            User::where('email', $employee->email)->update(['role' => $request->string('account_role')->toString()]);
        }

        return redirect()->route('employees.index')
            ->with('success', 'Employee details updated.');
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();

        return redirect()->route('employees.index')
            ->with('success', 'Employee removed.');
    }
}

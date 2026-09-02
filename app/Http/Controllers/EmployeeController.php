<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeRequest;
use App\Models\Award;
use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $perPage = (int) $request->query('per_page', 10);

        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        // Eager load the award so we can display "Poultry Award" instead of "ID: 1".
        $employees = Employee::with('award')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('noahface_id', 'like', "%{$search}%")
                        ->orWhere('employment_type', 'like', "%{$search}%")
                        ->orWhereHas('award', fn ($query) => $query->where('name', 'like', "%{$search}%"));
                });
            })
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return view('employees.index', compact('employees'));
    }

    public function create()
    {
        $awards = Award::orderBy('name')->get();

        return view('employees.create', compact('awards'));
    }

    public function store(StoreEmployeeRequest $request)
    {
        Employee::create($request->validated());

        return redirect()->route('employees.index')
            ->with('success', 'Employee linked successfully.');
    }

    public function edit(Employee $employee)
    {
        $awards = Award::orderBy('name')->get();

        return view('employees.edit', compact('employee', 'awards'));
    }

    public function update(StoreEmployeeRequest $request, Employee $employee)
    {
        $employee->update($request->validated());

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

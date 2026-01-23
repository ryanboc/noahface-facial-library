<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Award;
use App\Http\Requests\StoreEmployeeRequest;

class EmployeeController extends Controller
{
    public function index()
    {
        // Eager load the award so we can display "Poultry Award" instead of "ID: 1"
        $employees = Employee::with('award')->paginate(10);
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
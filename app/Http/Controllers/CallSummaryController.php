<?php

namespace App\Http\Controllers;
use App\Models\CallSummary;
use Illuminate\Http\Request;

class CallSummaryController extends Controller
{
    public function index()
    {
        $callSummaries = CallSummary::latest()->get();
        return view('admin.call-summary', compact('callSummaries'));
    }
}

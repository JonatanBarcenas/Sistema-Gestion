<?php

namespace App\Http\Controllers;

use App\Models\EmailLog;
use Illuminate\Http\Request;

class EmailController extends Controller
{
    public function index()
    {
        $emailLogs = EmailLog::latest()->paginate(10);
        return view('emails.index', compact('emailLogs'));
    }
}
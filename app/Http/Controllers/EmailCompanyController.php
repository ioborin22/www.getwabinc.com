<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\HelloEmail;

class EmailCompanyController extends Controller
{
    public function create()
    {
        return view('add-company');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'recipient_name' => 'nullable|string|max:255',
            'recipient_email' => 'required|email|unique:email_companies,recipient_email',
            'company_name' => 'nullable|string|max:255',
            'contract_id' => 'nullable|string|max:50',
            'contract_topic' => 'nullable|string|max:255',
            'contract_description' => 'nullable|string',
            'additional_details' => 'nullable|string',
            'contract_start_date' => 'nullable|date',
            'contract_end_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return redirect('add-company')
                ->withErrors($validator)
                ->withInput($request->except('password'));
        }

        DB::table('email_companies')->insert([
            'recipient_name' => $request->input('recipient_name'),
            'recipient_email' => $request->input('recipient_email'),
            'company_name' => $request->input('company_name'),
            'contract_id' => $request->input('contract_id'),
            'contract_topic' => $request->input('contract_topic'),
            'contract_description' => $request->input('contract_description'),
            'additional_details' => $request->input('additional_details'),
            'contract_start_date' => $request->input('contract_start_date'),
            'contract_end_date' => $request->input('contract_end_date'),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return redirect()->route('dashboard')->with('success', 'Company added successfully!');
    }

    public function unsubscribe(Request $request)
    {
        $email = $request->input('email');

        $exists = DB::table('email_companies')->where('recipient_email', $email)->exists();

        if ($exists) {
            DB::table('email_companies')->where('recipient_email', $email)
                ->update(['subscribe' => 1]);

            return view('mail.unsubscribe_success', ['email' => $email])
                ->with('success', 'You have been successfully unsubscribed.');
        } else {
            return back()->with('error', 'Email not found.');
        }
    }

    public function getDkim()
    {
        // Чтение содержимого файлов конфигурации DKIM
        $dkimKeyTable = shell_exec('cat /etc/opendkim/KeyTable');
        $dkimSigningTable = shell_exec('cat /etc/opendkim/SigningTable');
        $trustedHosts = shell_exec('cat /etc/opendkim/TrustedHosts');

        // Вывод этих данных на страницу
        return view('dkim', compact('dkimKeyTable', 'dkimSigningTable', 'trustedHosts'));
    }

}

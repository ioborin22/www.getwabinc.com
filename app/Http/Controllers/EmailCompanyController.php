<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class EmailCompanyController extends Controller
{
    public function create()
    {
        return view('add-company');
    }

    public function storeContract(Request $request)
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

        return redirect()->route('dashboard')->with('success', 'Contract added successfully!');
    }

    public function storeCompany(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'email' => 'required|email|unique:empstateweb_emails,email',
            'company_url' => 'nullable|string|max:50'
        ]);

        if ($validator->fails()) {
            return redirect('add-company')
                ->withErrors($validator)
                ->withInput($request->except('password'));
        }

        DB::table('empstateweb_emails')->insert([
            'name' => $request->input('name'),
            'company' => $request->input('company'),
            'email' => $request->input('email'),
            'company_url' => $request->input('company_url'),
            'subscribe' => 0,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return redirect()->route('dashboard')->with('success', 'Company added successfully!');
    }

    public function viewHelloEmail($id)
    {

        $company = DB::table('email_companies')->where('id', $id)->first();


        return view('mail.hello_email', ['company' => $company]);
    }

    public function viewAgainEmail($id)
    {

        $company = DB::table('email_companies')->where('id', $id)->first();


        return view('mail.hello_again', ['company' => $company]);
    }

    public function bussinesViewHelloEmail($id)
    {

        $company = DB::table('empstateweb_emails')->where('id', $id)->first();


        return view('mail.bussines.hello_email', ['company' => $company]);
    }

    public function bussinesViewAgainEmail($id)
    {

        $company = DB::table('empstateweb_emails')->where('id', $id)->first();


        return view('mail.bussines.hello_again', ['company' => $company]);
    }

    // Метод для редактирования компании
    public function edit($id)
    {
        $company = DB::table('email_companies')->where('id', $id)->first();

        if (!$company) {
            return redirect()->route('dashboard')->with('error', 'Company not found.');
        }

        return view('edit-company', ['company' => $company]);
    }

    // Метод для обновления компании
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'recipient_name' => 'nullable|string|max:255',
            'recipient_email' => 'required|email|unique:email_companies,recipient_email,' . $id,
            'company_name' => 'nullable|string|max:255',
            'contract_id' => 'nullable|string|max:50',
            'contract_topic' => 'nullable|string|max:255',
            'contract_description' => 'nullable|string',
            'additional_details' => 'nullable|string',
            'contract_start_date' => 'nullable|date',
            'contract_end_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return redirect()->route('edit-company', ['id' => $id])
                ->withErrors($validator)
                ->withInput();
        }

        DB::table('email_companies')->where('id', $id)->update([
            'recipient_name' => $request->input('recipient_name'),
            'recipient_email' => $request->input('recipient_email'),
            'company_name' => $request->input('company_name'),
            'contract_id' => $request->input('contract_id'),
            'contract_topic' => $request->input('contract_topic'),
            'contract_description' => $request->input('contract_description'),
            'additional_details' => $request->input('additional_details'),
            'contract_start_date' => $request->input('contract_start_date'),
            'contract_end_date' => $request->input('contract_end_date'),
            'updated_at' => now()
        ]);

        return redirect()->route('dashboard')->with('success', 'Company updated successfully!');
    }

    // Метод для удаления компании
    public function destroy($id)
    {
        DB::table('email_companies')->where('id', $id)->delete();
        return redirect()->route('dashboard')->with('success', 'Company deleted successfully.');
    }

    /**
     * Метод для отображения страницы отписки (GET-запрос)
     * Этот метод будет вызван при переходе по ссылке из письма
     */
    public function showUnsubscribePage(Request $request)
    {
        // Получаем email из URL-параметра ?email=example@mail.com
        $email = $request->query('email');

        // Проверяем, есть ли email в базе данных
        if (!$email) {
            return redirect()->back()->with('error', 'Email not provided.');
        }

        // Отображаем страницу отписки с email
        return view('mail.unsubscribe', ['email' => $email]);
    }

    /**
     * Метод для обработки отписки (POST-запрос)
     * Этот метод обрабатывает данные, полученные с JavaScript через POST
     */
    public function unsubscribe(Request $request)
    {
        // Получаем данные из запроса POST
        $email = $request->input('email');
        $screenResolution = $request->input('screen_resolution');
        $timeZone = $request->input('time_zone');
        $browserLanguage = $request->input('browser_language');
        $referrer = $request->input('referrer'); // Захват реферера

        // Логируем все данные для отладки
        Log::info('Unsubscribe Request Data:', [
            'email' => $email,
            'screen_resolution' => $screenResolution,
            'time_zone' => $timeZone,
            'browser_language' => $browserLanguage,
            'referrer' => $referrer,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        // Проверка наличия email в таблице email_companies
        $exists = DB::table('email_companies')->where('recipient_email', $email)->exists();

        if ($exists) {
            // Отписываем пользователя (устанавливаем subscribe в 1)
            DB::table('email_companies')->where('recipient_email', $email)->update(['subscribe' => 1]);

            // Проверяем, существует ли запись в unsubscribe_logs
            $unsubscribeLogExists = DB::table('unsubscribe_logs')->where('email', $email)->exists();

            if ($unsubscribeLogExists) {
                // Обновляем запись, если она уже существует
                DB::table('unsubscribe_logs')->where('email', $email)->update([
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'referrer' => $referrer,
                    'screen_resolution' => $screenResolution,
                    'time_zone' => $timeZone,
                    'browser_language' => $browserLanguage,
                    'unsubscribed_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                // Создаем новую запись, если такой записи нет
                DB::table('unsubscribe_logs')->insert([
                    'email' => $email,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'referrer' => $referrer,
                    'screen_resolution' => $screenResolution,
                    'time_zone' => $timeZone,
                    'browser_language' => $browserLanguage,
                    'unsubscribed_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return view('mail.unsubscribe_success', ['email' => $email])->with('success', 'You have been successfully unsubscribed.');
        } else {
            // Логируем сообщение, если email не найден
            Log::warning('Email not found for unsubscription:', ['email' => $email]);
            return back()->with('error', 'Email not found.');
        }
    }

    public function showUnsubscribeDetails($company_id)
    {
        // Fetch the company details by the given ID
        $company = DB::table('email_companies')->where('id', $company_id)->first();

        // Check if the company exists
        if (!$company) {
            return redirect()->back()->with('error', 'Company not found.');
        }

        // Fetch the unsubscribe log for the company based on email
        $unsubscribeLog = DB::table('unsubscribe_logs')->where('email', $company->recipient_email)->first();

        // Check if there's an unsubscribe record
        if (!$unsubscribeLog) {
            return view('mail.unsubscribe_details', ['message' => 'No unsubscription log found for this company.', 'company' => $company]);
        }

        // Pass the company and unsubscribe details to the view
        return view('mail.unsubscribe_details', ['unsubscribeLog' => $unsubscribeLog, 'company' => $company]);
    }

    public function logs()
    {
        // Путь к файлу логов helloemail
        $logPathHelloEmail = storage_path('logs/helloemail.log');
        // Путь к файлу логов againemail
        $logPathAgainEmail = storage_path('logs/againemail.log');
        // Путь к файлу логов lastemail
        $logPathLastEmail = storage_path('logs/lastemail.log');

        // Функция для получения последних 15 строк из файла
        $getLastLines = function ($logPath) {
            if (File::exists($logPath)) {
                // Чтение всех строк файла
                $lines = file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                // Возвращаем последние 15 строк и переворачиваем их, чтобы новые строки были сверху
                return array_reverse(array_slice($lines, -15));
            }
            return ['Log file not found.'];
        };

        // Получаем последние 15 строк из каждого файла
        $helloEmailLogs = $getLastLines($logPathHelloEmail);
        $againEmailLogs = $getLastLines($logPathAgainEmail);
        $lastEmailLogs = $getLastLines($logPathLastEmail);

        // Передаем логи в шаблон
        return view('logs', [
            'helloEmailLogs' => $helloEmailLogs,
            'againEmailLogs' => $againEmailLogs,
            'lastEmailLogs' => $lastEmailLogs
        ]);
    }

    // Метод для отображения логов HelloEmail
    public function showHelloEmailLogs()
    {
        // Путь к файлу логов
        $logPath = storage_path('logs/helloemail.log');

        // Проверяем, существует ли файл логов
        if (File::exists($logPath)) {
            // Читаем содержимое файла
            $logs = File::get($logPath);

            // Разделяем содержимое файла по строкам
            $logLines = explode(PHP_EOL, $logs);

            // Убираем пустые строки
            $logLines = array_filter($logLines);

            // Переворачиваем строки для реверсного отображения
            $logLines = array_reverse($logLines);

            // Преобразуем массив строк в коллекцию
            $logsCollection = collect($logLines);

            // Пагинация
            $perPage = 20; // Количество строк на одной странице
            $currentPage = LengthAwarePaginator::resolveCurrentPage();
            $currentPageLogs = $logsCollection->slice(($currentPage - 1) * $perPage, $perPage)->all();

            // Создаем пагинацию
            $paginatedLogs = new LengthAwarePaginator(
                $currentPageLogs,
                $logsCollection->count(),
                $perPage,
                $currentPage,
                ['path' => LengthAwarePaginator::resolveCurrentPath()]
            );

            return view('hello-email-logs', ['logs' => $paginatedLogs]);
        } else {
            return view('hello-email-logs', ['logs' => 'Log file not found.']);
        }
    }

    // Метод для отображения логов AgainEmail
    public function showAgainEmailLogs()
    {
        // Путь к файлу логов
        $logPath = storage_path('logs/againemail.log');

        // Проверяем, существует ли файл логов
        if (File::exists($logPath)) {
            // Читаем содержимое файла
            $logs = File::get($logPath);

            // Разделяем содержимое файла по строкам
            $logLines = explode(PHP_EOL, $logs);

            // Убираем пустые строки
            $logLines = array_filter($logLines);

            // Переворачиваем строки для реверсного отображения
            $logLines = array_reverse($logLines);

            // Преобразуем массив строк в коллекцию
            $logsCollection = collect($logLines);

            // Пагинация
            $perPage = 20; // Количество строк на одной странице
            $currentPage = LengthAwarePaginator::resolveCurrentPage();
            $currentPageLogs = $logsCollection->slice(($currentPage - 1) * $perPage, $perPage)->all();

            // Создаем пагинацию
            $paginatedLogs = new LengthAwarePaginator(
                $currentPageLogs,
                $logsCollection->count(),
                $perPage,
                $currentPage,
                ['path' => LengthAwarePaginator::resolveCurrentPath()]
            );

            return view('again-email-logs', ['logs' => $paginatedLogs]);
        } else {
            return view('again-email-logs', ['logs' => 'Log file not found.']);
        }
    }

    // Метод для отображения логов LastEmail
    public function showLastEmailLogs()
    {
        // Путь к файлу логов
        $logPath = storage_path('logs/lastemail.log');

        // Проверяем, существует ли файл логов
        if (File::exists($logPath)) {
            // Читаем содержимое файла
            $logs = File::get($logPath);

            // Разделяем содержимое файла по строкам
            $logLines = explode(PHP_EOL, $logs);

            // Убираем пустые строки
            $logLines = array_filter($logLines);

            // Переворачиваем строки для реверсного отображения
            $logLines = array_reverse($logLines);

            // Преобразуем массив строк в коллекцию
            $logsCollection = collect($logLines);

            // Пагинация
            $perPage = 20; // Количество строк на одной странице
            $currentPage = LengthAwarePaginator::resolveCurrentPage();
            $currentPageLogs = $logsCollection->slice(($currentPage - 1) * $perPage, $perPage)->all();

            // Создаем пагинацию
            $paginatedLogs = new LengthAwarePaginator(
                $currentPageLogs,
                $logsCollection->count(),
                $perPage,
                $currentPage,
                ['path' => LengthAwarePaginator::resolveCurrentPath()]
            );

            return view('last-email-logs', ['logs' => $paginatedLogs]);
        } else {
            return view('last-email-logs', ['logs' => 'Log file not found.']);
        }
    }
}

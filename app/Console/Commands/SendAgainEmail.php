<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\AgainEmail; // Мейлер для повторного письма
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SendAgainEmail extends Command
{
    protected $signature = 'send:againemail';
    protected $description = 'Sends a follow-up email one week after the hello email, excluding Fridays, Saturdays, and Sundays.';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Получаем одну компанию, которая получила hello_email неделю назад и еще не получила again_email
        $company = DB::table('email_companies')
                    ->whereNotNull('hello_email')
                    ->whereNull('hello_email_again')
                    ->where('subscribe', 0) // Дополнительная проверка на подписку
                    ->first();

        if ($company) {
            $helloEmailDate = Carbon::parse($company->hello_email);
            $now = Carbon::now();

            // Проверяем, прошла ли неделя и если сегодня не пятница, суббота или воскресенье
            if ($now->diffInDays($helloEmailDate) >= 7 && !$this->isWeekend($now)) {
                // Отправляем повторное письмо
                Mail::to($company->recipient_email)->send(new AgainEmail($company));

                // Обновляем запись в базе данных, чтобы отметить отправку повторного письма
                DB::table('email_companies')
                    ->where('id', $company->id)
                    ->update(['hello_email_again' => now()]);

                $this->info('Follow-up email sent to ' . $company->recipient_email);
            } else {
                $this->info('No eligible emails to send or today is a weekend.');
            }
        } else {
            $this->info('No companies found that require a follow-up email.');
        }
    }

    /**
     * Проверка на выходные (пятница, суббота, воскресенье)
     */
    private function isWeekend($date)
    {
        // Пятница = 5, Суббота = 6, Воскресенье = 0
        $dayOfWeek = $date->dayOfWeek;
        return $dayOfWeek === 5 || $dayOfWeek === 6 || $dayOfWeek === 0;
    }
}
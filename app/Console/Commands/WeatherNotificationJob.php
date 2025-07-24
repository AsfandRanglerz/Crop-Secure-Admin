<?php

namespace App\Console\Commands;

use App\Helpers\WeatherNotificationHelper;
use App\Models\CropInsurance;
use App\Models\EnsuredCropName;
use App\Models\InsuranceHistory;
use App\Models\VillageCrop;
use App\Models\VillageWeatherDailySummary;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class WeatherNotificationJob extends Command
{
    protected $signature = 'notify:weather-alerts';
    protected $description = 'Send weather alerts to farmers based on last 14 days of village weather data';

    public function handle()
    {
        dispatch(new \App\Jobs\ProcessWeatherNotificationJob());
        $this->info("✅ Queued weather notification job.");
    }
}

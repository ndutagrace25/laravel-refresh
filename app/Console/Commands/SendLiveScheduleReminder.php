<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Console\Command;
use App\Models\LiveScheduleGallery;
use App\Notifications\LiveScheduleCreatorNotification;

class SendLiveScheduleReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminder:live-schedule';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send live schedule reminder notifications';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Get Allowed Request Timeout
        $eligibleNotifiableTimeInMinutes = 30;

        // Fetch all eligible Live Schedules
        $eligibleLiveSchedules = LiveScheduleGallery::where('status', 0)->get();

        // Notify live schedulers if they have an upcoming live session
        if ($eligibleLiveSchedules->count() > 0) {
            foreach ($eligibleLiveSchedules as $liveSchedule) {
                $currentTimestampInScheduleTimezone = Carbon::now($liveSchedule->timezone);
                $liveScheduleDate = new Carbon($liveSchedule->date, $liveSchedule->timezone);
                $liveScheduleTime = new Carbon($liveSchedule->start_time, $liveSchedule->timezone);
                $liveScheduleDatetime = "{$liveScheduleDate->format('Y-m-d')} {$liveScheduleTime->format('H:i:s')}";
                $liveScheduleTimestamp = Carbon::createFromFormat('Y-m-d H:i:s', $liveScheduleDatetime, $liveSchedule->timezone);
                $timeDifferenceInMinutesToLiveSession = $liveScheduleTimestamp->diffInMinutes($currentTimestampInScheduleTimezone);

                if ($timeDifferenceInMinutesToLiveSession <= $eligibleNotifiableTimeInMinutes) {
                    $liveScheduler = User::where('id', $liveSchedule->user_id)->first();
                    if ($liveScheduler) {
                        $reminderNotificationData = [
                            'user' => $liveScheduler,
                            'message' => "Hi {$liveScheduler->username}, your '{$liveSchedule->title}' live schedule is starting in less than 30 min."
                        ];
                        $liveScheduler->notify(new LiveScheduleCreatorNotification($reminderNotificationData));
                    }
                }
            }

            return Command::SUCCESS;
        }
    }
}

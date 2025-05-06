<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TimeZoneController extends Controller
{
    //
    public function index(Request $request){
       
        $timezones = [
            '0' => 'GMT Greenwich Mean Time (GMT)',
            '1' => 'UTC Universal Coordinated Time (GMT)',
            '2' => 'ECT European Central Time (GMT+1:00)',
            '3' => 'EET Eastern European Time (GMT+2:00)',
            '4' => 'ART (Arabic) Egypt Standard Time (GMT+2:00)',
            '5' => 'EAT Eastern African Time (GMT+3:00)',
            '6' => 'MET Middle East Time (GMT+3:30)',
            '7' => 'NET Near East Time (GMT+4:00)',
            '8' => 'PLT Pakistan Lahore Time (GMT+5:00)',
            '9' => 'IST India Standard Time (GMT+5:30)',
            '10' => 'BST Bangladesh Standard Time (GMT+6:00)',
            '11' => 'VST Vietnam Standard Time (GMT+7:00)',
            '12' => 'CTT China Taiwan Time (GMT+8:00)',
            '13' => 'JST Japan Standard Time (GMT+9:00)',
            '14' => 'ACT Australia Central Time (GMT+9:30)',
            '15' => 'AET Australia Eastern Time (GMT+10:00)',
            '16' => 'SST Solomon Standard Time (GMT+11:00)',
            '17' => 'NST New Zealand Standard Time (GMT+12:00)',
            '18' => 'MIT Midway Islands Time (GMT-11:00)',
            '19' => 'HST Hawaii Standard Time (GMT-10:00)',
            '20' => 'AST Alaska Standard Time (GMT-9:00)',
            '21' => 'PST Pacific Standard Time (GMT-8:00)',
            '22' => 'PNT Phoenix Standard Time (GMT-7:00)',
            '23' => 'MST Mountain Standard Time (GMT-7:00)',
            '24' => 'CST Central Standard Time (GMT-6:00)',
            '25' => 'EST Eastern Standard Time (GMT-5:00)',
            '26' => 'IET Indiana Eastern Standard Time (GMT-5:00)',
            '27' => 'PRT Puerto Rico and US Virgin Islands Time (GMT-4:00)',
            '28' => 'CNT Canada Newfoundland Time (GMT-3:30)',
            '29' => 'AGT Argentina Standard Time (GMT-3:00)',
            '30' => 'BET Brazil Eastern Time (GMT-3:00)',
            '31' => 'CAT Central African Time (GMT-1:00)',
        ];
        
           
        return response()->json([
            "success" => true,
            "timezones" => $timezones
        ]);
    }
}

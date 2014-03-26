<?php

namespace app\controllers;
use BaseController, Form, Input, Redirect;
use Sentry, View, Log, Cache, Config, DB, Date;
use App, Former, Datatables, Asset, Time, Vd\Vd;

// Models
use app\models\Client;
use app\models\Job;
use app\models\Userspermissions;

class ClientsController extends BaseController
{
    public $clientSelectBox = array();

    public function __construct()
    {
        parent::__construct();

        Asset::add('select2', 'assets/css/select2.css');
        Asset::add('daterangepicker', 'assets/css/daterangepicker.css');

        Asset::add('select2min', 'assets/js/select2.min.js');
        Asset::add('amcharts', 'assets/js/amcharts.js');
        Asset::add('amchartsSerial', 'assets/js/serial.js');

        Asset::add('momentmin', 'assets/js/moment.min.js');
        Asset::add('daterangepicker', 'assets/js/daterangepicker.js');
        Asset::container('footer')->add('clients', 'assets/js/clients.js');

    }

    public function clients($client=null)
    {

        $start = Input::get('start', Date::forge('last day')->format('date'));
        
        $end   = Input::get('end',   Date::forge('today')->format('date'));

        $clientselected = Input::get('Client', $client);
        $client = Client::where('clientid', '=', $clientselected)->first();

        $user=Sentry::getUser();

        // Get Clients to fill the Client Select Box And Select Values From Permissions
        $permissions = Userspermissions::where('id', '=', $user->id)->first();
        if ($permissions<>null) {
            $permissions=unserialize($permissions->clients);
            $clients = Client::wherein('clientid', $permissions)->get();
            $clientSelectBox=Client::clientSelectBox($clients->toArray());
         } else { 
            $clientSelectBox=Client::clientSelectBox();
        }
        ///// End Permissions

        if ($client == Null) {
            $platform="";
            $fileretension="";
            $jobretension="";
            $autoprune="";
            $terminatedJobs="0";
            $cancelJobs="0";
            $cancelJobs="0";
            $runningJobs="0";
            $wattingJobs="0";
            $errorJobs="0";
            // Indicates Failed and Okay Jobs
            $nTransBytes="0";
            $nTransFiles="0";
            $graphOkJob="0";
            $graphFailedJob="0";

        } else {

            $platform=$client->Uname;
            $autoprune=$client->AutoPrune;

            /* Calculate the Retension Period */
            $to =Date::now();
            $text=" Days";
            $fileretension = Date::now()->sub( $client->fileretention.' seconds').$text;

            if ($fileretension > 365) {
                $type = ' Year';
                $fileretension=intval($fileretension/365).$type;
            }

            $jobretension =Date::now()->sub( $client->jobretention.' seconds').$text; 


            #= Time::diff(Date::forge(Date::now()->sub( $client->jobretention.' seconds'))->format('short'), $to, $type = 'days').$text;

            if ($jobretension > 365) {
                $type = ' Year';
                $jobretension=intval($jobretension/365).$type;
            }

            /* Get Terminated Jobs */
            $tjobs = Job::where('jobstatus','=', 'T')
                      ->where('starttime',  '>=',  $start)
                      ->where('endtime',    '<=',    $end)
                      ->where('clientid','=',$client->clientid)
                      ->get();

            // Number Terminate Jobs
            $terminatedJobs=count($tjobs);

                /* Get Canceled Jobs */
            $canceledJobs = Job::where('jobstatus','=', 'A')
                    ->where('starttime','>=',$start)
                    ->where('endtime','<=',$end)
                    ->where('clientid','=',$client->ClientId)
                    ->get();

            // Number Terminate Jobs
            $cancelJobs=count($canceledJobs);

                /* Get Canceled Jobs */
            $canceledJobs = Job::where('jobstatus','=', 'A')
                    ->where('starttime','>=',$start)
                    ->where('endtime','<=',$end)
                    ->where('clientid','=',$client->ClientId)
                    ->get();

            // Number Terminate Jobs
            $cancelJobs=count($canceledJobs);

                 /* Get Running Jobs */
            $runJobs = Job::where('jobstatus','=', 'R')
                    ->where('starttime','>=',$start)
                    ->where('endtime','<=',$end)
                    ->where('clientid','=',$client->ClientId)
                    ->get();

            // Number Running Jobs
            $runningJobs=count($runJobs);

             /* Get Watting Jobs */
            $wateJobs = Job::wherein('jobstatus', array('c', 'F', 'j','M','m','p','s','t'))
                    ->where('endtime','<=',$end)
                    ->where('starttime','>=',$start)
                    ->where('clientid','=',$client->ClientId)
                    ->get();

            // Number Watting Jobs
            $wattingJobs=count($wateJobs);

              /* Get Error Jobs */
            $errJobs = Job::wherein('jobstatus', array('e', 'f', 'E'))
                    ->where('starttime','>=',$start)
                    ->where('endtime','<=',$end)
                    ->where('clientid','=',$client->ClientId)
                    ->get();

            // Number Error Jobs
            $errorJobs=count($errJobs);
            $nTransBytes=0;
            $nTransFiles=0;
            $terminatedJobs=count($tjobs);

            /* Calculate Jobs and Bytes */
            $tjobs=$tjobs->toArray();
            $nTransFiles = array_sum( array_fetch ($tjobs, 'jobfiles')) ;
            $nTransBytes = array_sum( array_fetch ($tjobs, 'jobbytes') );

            // Value for The Graphs
            $graphOkJob     = ($terminatedJobs <> 0) ?  ($terminatedJobs/($terminatedJobs+$errorJobs))*100 : 0 ;
            $graphFailedJob = ($errorJobs<> 0) ? ($errorJobs/($errorJobs+$terminatedJobs))*100 : 0;

            $nTransFiles=preg_replace("/(?<=\d)(?=(\d{3})+(?!\d))/",",",$nTransFiles);
            $nTransBytes=$this->byte_format($nTransBytes);

        }

        /* Draws Files Graph */
        $graphFiles = DB::table('job')
                  ->where('clientid','=', $clientselected)
                  ->where('starttime','>=',  $start)
                  ->where('endtime','<=',    $end)
                  ->orderby('starttime', 'asc')
                  ->get(array( DB::raw('date(job.starttime) as date'), DB::raw('jobfiles as files') ));
        $graphFiles= json_encode((array) $graphFiles);

        /* Draws Bytes Graph */
        $graphBytes = DB::table('job')->where('clientid','=', $clientselected)
                  ->where('starttime','>=',  $start)
                  ->where('endtime','<=',$end)
                  ->orderby('starttime', 'asc')
                  ->get(array(DB::raw('date(job.starttime) as date'),DB::raw('jobbytes as bytes')));

        $graphBytes = json_encode((array) $graphBytes);

        Former::populate( array('date' => $start .' - '.$end),
                          array('Client' => $clientselected  )   );

        return View::make('clients', array(
                                    'terminatedJobs' => $terminatedJobs,
                                    'nTransFiles'    => $nTransFiles,
                                    'cancelJobs'     => $cancelJobs,
                                    'runningJobs'    => $runningJobs,
                                    'wattingJobs'    => $wattingJobs,
                                    'errorJobs'      => $errorJobs,
                                    'nTransBytes'    => $nTransBytes,
                                    'start'          => $start,
                                    'end'            => $end,
                                    'type'           => 'terminated',
                                    'clientSelected' => $clientselected,
                                    'graphOkJob'     => $graphOkJob,
                                    'graphFailedJob' => $graphFailedJob,
                                    'platform'       => $platform,
                                    'fileretension'  => $fileretension,
                                    'jobretension'   => $jobretension,
                                    'autoprune'      => $autoprune,
                                    'clientSelectBox' =>  $clientSelectBox,
                                    'graphFiles'      => $graphFiles,
                                    'graphBytes'      => $graphBytes,
                                )
                         );

    }

    /*Gets Data from the Client */

    public function getclients()
    {

        $start = Input::get('start', Date::forge('today')->format('date'));
        $end   = Input::get('end',   Date::forge('today')->format('date'));

        $client = Client::where('clientid', '=', Input::get('Client'))->first();

        $tjobs = Job::select(array('jobid','name','starttime','endtime',
                                  'level','jobbytes','jobfiles','jobstatus'))
                //  ->join('jobmedia','jobmedia.jobid', '=', 'job.jobid')
                  ->where('clientid','=',$client->clientid)
                  ->where('starttime','>=',  $start)
                  ->where('endtime','<=',$end);

        switch (Input::get('type')) {
            case "terminated":
                $tjobs->where('jobstatus','=', 'T');
                break;
            case "running":
                $tjobs->where('jobstatus','=', 'R');
                break;
            case "watting":
                $tjobs->wherein('jobstatus', array('c', 'F', 'j','M','m','p','s','t'));
                break;
            case "error":
                $tjobs->wherein('jobstatus', array('e', 'f', 'E'));
                break;
            case "cancel":
                $tjobs->where('jobstatus','=', 'A');
                break;
        }

        return Datatables::of($tjobs)
                            ->edit_column('name', '{{ link_to_route("jobs", $name ,array("Job" => $jobid)) }} ')
                            ->edit_column('jobid', '{{ link_to_route("jobs", $jobid ,array("Job" => $jobid)) }} ')
                            ->make();

    }

}
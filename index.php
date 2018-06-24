<?php

error_reporting( E_ALL ^ E_DEPRECATED ^ E_NOTICE );
ini_set( 'display_errors', 1 );



// Get the user parameter. If it is omitted, we show all users to be selected
$param_user_id = isset($_GET['uid']) ? $_GET['uid'] : NULL;
$output = isset($_GET['opt']) ? $_GET['opt'] : 'ALL';

$OUTPUT_USERS = 'users';
$OUTPUT_PUNCHES = 'punches';



// JSON URLs
$URL_LOCATIONS = 'https://www.henriqueangelo.com/7shifts/json/locations.json';
$URL_USERS = 'https://www.henriqueangelo.com/7shifts/json/users.json';
$URL_TIMEPUNCHES = 'https://www.henriqueangelo.com/7shifts/json/timePunches.json';

// Get the content of the URLs into variables
$json_content_locations = file_get_contents($URL_LOCATIONS);
$json_content_users = file_get_contents($URL_USERS);
$json_content_timepunches = file_get_contents($URL_TIMEPUNCHES);

// COnvert Json to array
$array_content_locations = json_decode($json_content_locations, true);
$array_content_users = json_decode($json_content_users, true);
$array_content_timepunches = json_decode($json_content_timepunches, true);



/**************************************************************************************************
Location class
**************************************************************************************************/

class LocationClass {
    public $address = '';
    public $city = '';
    public $country = '';
    public $created = '';
    public $id = '';
    //public $labour_settings = '';
    public $latitude = '';
    public $longitude = '';
    public $modified = '';
    public $state = '';
    public $timezone = '';

    public $daily_overtime_multiplier = '';
    public $daily_overtime_threshold = '';

    public $weekly_overtime_multiplier = '';
    public $weekly_overtime_threshold = '';

    public function __construct($location)
    {
        $this->address = $location['address'];
        $this->city = $location['city'];
        $this->country = $location['country'];
        $this->created = $location['created'];
        $this->id = $location['id'];
        $this->latitude = $location['lat'];
        $this->longitude = $location['lng'];
        $this->modified = $location['modified'];
        $this->state = $location['state'];
        $this->timezone = $location['timezone'];

        $this->daily_overtime_multiplier = $location['labourSettings']['dailyOvertimeMultiplier'];
        $this->daily_overtime_threshold = $location['labourSettings']['dailyOvertimeThreshold'];

        $this->weekly_overtime_multiplier = $location['labourSettings']['weeklyOvertimeMultiplier'];
        $this->weekly_overtime_threshold = $location['labourSettings']['weeklyOvertimeThreshold'];
    }
}


/**************************************************************************************************
User class
**************************************************************************************************/

class UserClass {
    public $active = '';
    public $created = '';
    public $email = '';
    public $first_name = '';
    public $hourly_wage = '';
    public $id = '';
    public $lastName = '';
    public $location_id = '';
    public $modified = '';
    public $photo = '';
    public $user_type = '';

    public function __construct($user)
    {
        $this->active = $user['active'];
        $this->created = $user['created'];
        $this->email = $user['email'];
        $this->first_name = $user['firstName'];
        $this->hourly_wage = $user['hourlyWage'];
        $this->id = $user['id'];
        $this->last_name = $user['lastName'];
        $this->location_id = $user['locationId'];
        $this->modified = $user['modified'];
        $this->photo = $user['photo'];
        $this->user_type = $user['userType'];
    }
}


/**************************************************************************************************
Time Punch class
**************************************************************************************************/

class TimePunchClass {
    public $clocked_in = '';
    public $clocked_out = '';
    public $created = '';
    public $hourly_wage = '';
    public $id = '';
    public $location_id = '';
    public $modified = '';
    public $user_id = '';

    public $datetime_in;
    public $datetime_out;
    public $workday; // difference between datetime_in and datetime_out
    public $workday_hours; // workday represented in hours
    public $workday_minutes; // workday represented in minutes

    public function __construct($punch)
    {
        $this->clocked_in = $punch['clockedIn'];
        $this->clocked_out = $punch['clockedOut'];
        $this->created = $punch['created'];
        $this->hourly_wage = $punch['hourlyWage'];
        $this->id = $punch['id'];
        $this->location_id = $punch['locationId'];
        $this->modified = $punch['modified'];
        $this->user_id = $punch['userId'];

        // convert date from string to date object
        $datetime = new DateTime();
        $this->datetime_in = $datetime-> createFromFormat('Y-m-d H:i:s', $this->clocked_in);

        $datetime = new DateTime();
        $this->datetime_out = $datetime-> createFromFormat('Y-m-d H:i:s', $this->clocked_out);

        // calculate the workday
        $this->workday = $this->datetime_in->diff($this->datetime_out);

        // calculate the workday in minutes
        $minutes = $this->workday->days * 24 * 60;
        $minutes += $this->workday->h * 60;
        $minutes += $this->workday->i;
        $this->workday_minutes = $minutes;

        // calculate workday in hours
        $this->workday_hours = $minutes / 60;
    }
}


/**************************************************************************************************
Methos to populate classes
**************************************************************************************************/

// Populate the locations
$locations = array();
foreach ($array_content_locations as $location) {
    $locations[] = new LocationClass($location);
}

// populate the users
$users = array();
foreach ($array_content_users as $user_id) {
    foreach ($user_id as $user) {
        $users[] = new UserClass($user);
    }
}

// populate the users
$time_punches = array();
foreach ($array_content_timepunches as $punch) {
    $time_punches[] = new TimePunchClass($punch);
}


/**************************************************************************************************
Utils
**************************************************************************************************/

function GetUserById($id)
{
    /**
    Get the user object according to the given ID

    :param $id:
        The Id of the User

    :return UserClass:
        The user class relatd to the user Id.
        FALSE if not found
    */

    global $users;

    $qt = count($users);

    $found = false;
    $user_id = null;

    for ($i = 0; $i < $qt && !$found; $i++){
        if ($users[$i]->id == $id) {
            $found = true;
            $user_id = $i;
        }
    }

    return $found ? $users[$user_id] : false;
}


function GetLocationById($id)
{
    /**
    Get the Location object according to the given ID

    :param $id:
        The Id of the Location

    :return LocationClass:
        The Location class relatd to the Location Id.
        FALSE if not found
    */

    global $locations;

    $qt = count($locations);

    $found = false;
    $location_id = null;

    for ($i = 0; $i < $qt && !$found; $i++){
        if ($locations[$i]->id == $id) {
            $found = true;
            $location_id = $i;
        }
    }

    return $found ? $locations[$location_id] : false;
}


function GetPunchesFromUserId($id)
{
    /**
    Return a list of time punches related to a user

    :param $id:
        The Id of the User

    :return Array of TimePunchClass:
        A list of time punches related to the given user.
    */

    global $time_punches;

    $qt = count($time_punches);

    $punches = array();

    for ($i = 0; $i < $qt; $i++) {
        if ($time_punches[$i]->user_id == $id) {
            $punches[] = $time_punches[$i];
        }
    }

    return $punches;

}


/**************************************************************************************************
Javascript code
**************************************************************************************************/

$scripts ='
<script>

var req_user = ( window.XMLHttpRequest ) ? new XMLHttpRequest() : new ActiveXObject( "Microsoft.XMLHTTP" );
function LoadUser(id) {
    req_user.open( \'GET\', \'/7shifts/index.php?uid=\' + id + \'&opt=punches\', true );
    req_user.setRequestHeader( "Content-type", "application/x-www-form-urlencoded" );
    req_user.send();
    document.getElementById(\'user_content\').innerHTML = \'Loading content, please wait...\';
}

req_user.onreadystatechange = func__req_user;
function func__req_user() {
    if ( this.readyState == 4 && this.status == 200 ) {
        var resp = String( this.responseText );
        document.getElementById(\'user_content\').innerHTML = resp;
    }
}



function LoadIndex() {
    window.location.href = \'/7shifts/\';
}

function getXmlDoc() {
    return ( window.XMLHttpRequest ) ? new XMLHttpRequest() : new ActiveXObject( "Microsoft.XMLHTTP" );
}
</script>
';


/**************************************************************************************************
Main program
**************************************************************************************************/

if (!$param_user_id) {

    // A list of users to be selected
    $table_users = '<div style="width:420px; position:fixed; top:10px; bottom:10px; left:10px; overflow:auto;"><table border="0" width="400">';
    foreach ($users as $user) {
        $table_users .= '
            <tr>
                <td><img src="'.$user->photo.'" style="height:60;" /></td>
                <td>'.$user->id.'<br>'.$user->last_name.', '.$user->first_name.' ('.$user->email.')</td>
                <td><button onclick="LoadUser('.$user->id.')">Select</button></td>
            </tr>
        ';
    }
    $table_users .= '</table></div>';

} else {


    // get the user content
    $user = GetUserById($param_user_id);
    $html_user = '
        Id: '.$user->id.'<br>
        Name: '.$user->last_name.', '.$user->first_name.'<br>
        Email: '.$user->email.'<br>
        Hourly wage: '.$user->hourly_wage.' $/h<br>
        <img src="'.$user->photo.'" /><br>
    ';


    // Get the list of the user punches

    $location_punches = array(); // punches on each location

    $punches = GetPunchesFromUserId($param_user_id);
    $qt_user_punches = count($punches);
    $html_punches = '<table border="0">';
    for ($i = 0; $i < $qt_user_punches; $i++) {

        // populate the HTML for all time punches
        $html_punches .= '
            <tr>
                <td>'.$punches[$i]->datetime_in->format('Y-m-d H:i').'</td>
                <td>'.$punches[$i]->datetime_out->format('Y-m-d H:i').'</td>
                <td>'.'$'.number_format(($punches[$i]->workday_hours * $user->hourly_wage), 2).' ('.number_Format($punches[$i]->workday_hours, 2).' * $'.number_format($user->hourly_wage, 2).')</td>
                <td>'.$punches[$i]->location_id.'</td>
            </tr>
        ';

        // location punches
        $location = $punches[$i]->location_id;
        if (!array_key_exists($location, $location_punches)) {
            $location_punches[$location] = array(
                'punches' => array(), // list of punches
                'week' => array(),
                'day' => array(),
                'workday' => 0,
                'wage' => 0,
                'overtime' => 0
            ); // array of time punches; total workday; total wage; $ for day overtime; $ for week overtime
        }
        //$location_punches[$location]['punches'][] = $punches[$i];
        $location_punches[$location]['workday'] += $punches[$i]->workday_minutes;
        $location_punches[$location]['wage'] += ($punches[$i]->workday_hours * $user->hourly_wage);

        // week punches
        $week_number = $punches[$i]->datetime_in->format('Y-m-d W');
        if (!array_key_exists($week_number, $location_punches[$location]['week'])) {
            $location_punches[$location]['week'][$week_number] = array(
                'punches' => array(),  // array of time punches; total workday; total wage; $ for day overtime; $ for week overtime
                'workday',
                'wage',
                'overtime'
            );
        }
        $location_punches[$location]['week'][$week_number]['punches'][] = $punches[$i];
        $location_punches[$location]['week'][$week_number]['workday'] += $punches[$i]->workday_minutes;
        $location_punches[$location]['week'][$week_number]['wage'] += ($punches[$i]->workday_hours * $user->hourly_wage);

        // day punches
        $day_number = $punches[$i]->datetime_in->format('Y-m-d');
        if (!array_key_exists($day_number, $location_punches[$location])) {
            $location_punches[$location]['day'][$day_number] = array(
                'punches' => array(), // array of time punches; total workday; total wage; $ for day overtime; $ for week overtime
                'workday',
                'wage',
                'overtime'
            );
        }
        $location_punches[$location]['day'][$day_number]['punches'][] = $punches[$i];
        $location_punches[$location]['day'][$day_number]['workday'] += $punches[$i]->workday_minutes;
        $location_punches[$location]['day'][$day_number]['wage'] += ($punches[$i]->workday_hours * $user->hourly_wage);
    }
    $html_punches .= '</table>';




    //
    foreach ($location_punches as $location_id => $location) {

        $total_workhour = 0;
        $total_wage = 0;
        $total_overtime = 0;


        // calculate week overtime
        foreach ($location['week'] as $week_number => $week) {
            if ($week['workday'] > 2400) {
                $location_punches[$location_id]['week'][$week_number]['wage'] = 2400 * ($user->hourly_wage / 60);
                $location_punches[$location_id]['week'][$week_number]['overtime'] = ($week['workday'] - 2400) * ($user->hourly_wage / 60) * 1.5;
            }
        }

        // calculate day overtime
        foreach ($location['day'] as $day_number => $day) {
            if ($day['workday'] > GetLocationById($location_id)->daily_overtime_multiplier) {
                $location_punches[$location_id]['day'][$day_number]['wage'] = GetLocationById($location_id)->daily_overtime_multiplier * ($user->hourly_wage / 60);
                $location_punches[$location_id]['day'][$day_number]['overtime'] = ($day['workday'] - GetLocationById($location_id)->daily_overtime_multiplier) * ($user->hourly_wage / 60) * GetLocationById($location_id)->daily_overtime_threshold;
            }
            $total_workhour += $day['workday'];
            $total_wage += $location_punches[$location_id]['day'][$day_number]['wage'];
            $total_overtime += $location_punches[$location_id]['day'][$day_number]['overtime'];
        }

        $location_punches[$location_id]['workday'] = $total_workhour;
        $location_punches[$location_id]['wage'] = $total_wage;
        $location_punches[$location_id]['overtime'] = $total_overtime;
    }


    // represent time punches over each week number

    $html_punches = '<table border="0">';
    foreach ($location_punches as $location_id => $location) {

        $html_punches .= '<tr><td style="padding:20px 0 0 0; font-size:1.5em;">Location</td></tr>';
        $html_punches .= '<tr>
            <td style="padding-top:20px;"><b>Location:</b> '.$location_id.'</td>
            <td style="padding-top:20px;"><b>Workday:</b> '.$location['workday'].'</td>
            <td style="padding-top:20px;"><b>Wage:</b> $'.number_format($location['wage'], 2).'</td>
            <td style="padding-top:20px;"><b>Overtime:</b> $'.number_format($location['overtime'], 2).'</td>
            <td style="padding-top:20px;"><b>Total:</b> $'.number_format($location['wage'] + $location['overtime'], 2).'</td>
        </tr>';

        $html_punches .= '<tr><td style="padding:20px 0 0 0; font-size:1.5em;">On each week</td></tr>';
        foreach ($location['week'] as $week_number => $week) {

            // each week title
            $html_punches .= '<tr>
                <td style="padding-top:20px;"><b>Week number:</b> '.$week_number.'</td>
                <td style="padding-top:20px;"><b>Workday:</b> '.$week['workday'].'</td>
                <td style="padding-top:20px;"><b>Wage:</b> $'.number_format($week['wage'], 2).'</td>
                <td style="padding-top:20px;"><b>Overtime:</b> $'.number_format($week['overtime'], 2).'</td>
                <td style="padding-top:20px;"><b>Total:</b> $'.number_format($week['wage'] + $week['overtime'], 2).'</td>
            </tr>';

            // all time punches on each week
            $qt_week_punches = count($week['punches']);
            for ($i = 0; $i < $qt_week_punches; $i++) {
                $html_punches .= '
                    <tr>
                        <td>'.$week['punches'][$i]->datetime_in->format('Y-m-d H:i').'</td>
                        <td>'.$week['punches'][$i]->datetime_out->format('Y-m-d H:i').'</td>
                        <td>'.number_Format($week['punches'][$i]->workday_hours, 2).'</td>
                        <td>'.GetLocationById($week['punches'][$i]->location_id)->city.'</td>
                    </tr>
                ';
            }
        }


        // represent time punches over each day number

        $html_punches .= '<tr><td style="padding:20px 0 0 0; font-size:1.5em;">On each day</td></tr>';
        foreach ($location['day'] as $day_number => $day) {

            // each day punches
            $html_punches .= '<tr>
                <td style="padding-top:20px;"><b>Day number:</b> '.$day_number.'</td>
                <td style="padding-top:20px;"><b>Workday:</b> '.$day['workday'].'</td>
                <td style="padding-top:20px;"><b>Wage:</b>$ '.number_format($day['wage'], 2).'</td>
                <td style="padding-top:20px;"><b>Overtime:</b>$ '.number_format($day['overtime'], 2).'</td>
                <td style="padding-top:20px;"><b>Total:</b>$ '.number_format($day['wage'] + $day['overtime'], 2).'</td>
            </tr>';

            // all time punches on each day
            $qt_day_punches = count($day[0]);
            for ($i = 0; $i < $qt_day_punches; $i++) {
                $html_punches .= '
                    <tr>
                        <td>'.$day['punches'][$i]->datetime_in->format('Y-m-d H:i').'</td>
                        <td>'.$day['punches'][$i]->datetime_out->format('Y-m-d H:i').'</td>
                        <td>'.number_Format($day['punches'][$i]->workday_hours, 2).'</td>
                        <td>'.GetLocationById($day['punches'][$i]->location_id)->city.'</td>
                    </tr>
                ';
            }
        }
        $html_punches .= '</table>';
    }

}


/**************************************************************************************************
Output
**************************************************************************************************/

echo $scripts;

if (!$param_user_id) {
    echo $table_users;
    echo '<div id="user_content" style="margin:0 0 0 460px;"></div>';
}
else {
    if ($output != $OUTPUT_PUNCHES) {
        echo '<button onclick="LoadIndex();">Back to index</button><br><br>';
    }
    echo $html_user;
    //echo $html_punches;
    echo '<br>';
    echo '<div style="margin:40px 0 0 0; font-size:2em; font-weight:bold;">Punches</div>';
    echo $html_punches;}
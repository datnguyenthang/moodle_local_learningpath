<?php
require_once("$CFG->libdir/externallib.php");
//require_once($CFG->dirroot . "/local/learningpath/lib.php");
class local_learningpath_external extends external_api {

    //Add new line.
    public static function add_line_parameters() {
        return new external_function_parameters(
            array(
                'lpt_id' => new external_value(PARAM_INT, 'Learning Plan ID', VALUE_DEFAULT, 0),
                'course_id' => new external_value(PARAM_INT, 'Courses ID', VALUE_DEFAULT, null),
                'catalogue_id' => new external_value(PARAM_INT, 'Category ID', VALUE_DEFAULT, null),
                'module_id' => new external_value(PARAM_INT, 'Module ID', VALUE_DEFAULT, null),
                'sortorder' => new external_value(PARAM_INT, 'Sort Order', VALUE_DEFAULT, null),
                'required' => new external_value(PARAM_INT, 'Is Required? 0:No, 1:Yes', VALUE_DEFAULT, 1),
            )
        );
    }
    public static function add_line($lpt_id, $course_id, $catalogue_id, $module_id, $sortorder, $required) {
        global $DB, $CFG, $USER;

        $params = self::validate_parameters(self::add_line_parameters(), array(
            'lpt_id' => $lpt_id,
            'course_id' => $course_id,
            'catalogue_id' => $catalogue_id,
            'module_id' => $module_id,
            'sortorder' => $sortorder,
            'required' => $required,
        ));

        $data = new stdClass();
        $data->lpt_id = $params['lpt_id'];
        $data->course_id = $params['course_id'];
        $data->catalogue_id = $params['catalogue_id'];
        $data->module_id = $params['module_id'];
        $data->sortorder = $params['sortorder'];
        $data->assignee_id = $USER->id;
        $data->required = $params['required'];

        $DB->insert_record('local_learningpath_lines', $data);

        return true;
    }
    public static function add_line_returns() {
        return new external_value(PARAM_BOOL, 'True if success, false otherwise');
    }

    //Remove line.
    public static function remove_line_parameters() {
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'ID of the line to remove'),
            )
        );
    }
    public static function remove_line($id) {
        global $DB;

        $params = self::validate_parameters(self::remove_line_parameters(), array(
            'id' => $id,
        ));

        $DB->delete_records('local_learningpath_lines', array('id' => $params['id']));

        return true;
    }
    public static function remove_line_returns() {
        return new external_value(PARAM_BOOL, 'True if success, false otherwise');
    }

    //Update require line
    public static function change_line_required_parameters() {
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'ID of the line to update'),
                'required' => new external_value(PARAM_INT, 'Is Required? 0:No, 1:Yes'),
            )
        );
    }
    public static function change_line_required($id, $required) {
        global $DB;

        $params = self::validate_parameters(self::change_line_required_parameters(), array(
            'id' => $id,
            'required' => $required,
        ));

        $data = new stdClass();
        $data->id = $params['id'];
        $data->required = $params['required'];

        $DB->update_record('local_learningpath_lines', $data);

        return true;
    }
    public static function change_line_required_returns() {
        return new external_value(PARAM_BOOL, 'True if success, false otherwise');
    }

    //Search users
    public static function search_users_parameters() {
        return new external_function_parameters(
            array(
                'search' => new external_value(PARAM_TEXT, 'Search string'),
            )
        );
    }
    public static function search_users($search) {
        global $DB;
    
        $params = self::validate_parameters(self::search_users_parameters(), array(
            'search' => $search,
        ));
    
        $searchTerm = '%' . $params['search'] . '%';
    
        $users = $DB->get_records_sql(
                "SELECT id, username, email, CONCAT(firstname, ' ', lastname) AS fullname 
                    FROM {user} 
                WHERE username LIKE :username 
                OR email LIKE :email 
                OR firstname LIKE :firstname 
                OR lastname LIKE :lastname",
                array(
                    'username' => $searchTerm,
                    'email' => $searchTerm,
                    'firstname' => $searchTerm,
                    'lastname' => $searchTerm,
                )
            );
        $usersData = [];
        foreach($users as $user) {
            $usersData[] = [
                'id' => $user->id,
                'profileimageurl' => $CFG->wwwroot . '/user/pix.php/' . $user->id . '/f1.jpg',
                'email' => $user->email,
                'username' => $user->username,
                'fullname' => $user->fullname,
            ];
        }
    
        return array_values($usersData);
    }
    
    public static function search_users_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'User ID'),
                    'profileimageurl' => new external_value(PARAM_URL, 'Profile Image URL'),
                    'email' => new external_value(PARAM_TEXT, 'Username'),
                    'username' => new external_value(PARAM_TEXT, 'Username'),
                    'fullname' => new external_value(PARAM_TEXT, 'Full Name'),
                )
            )
        );
    }

    //Add user
    public static function add_user_parameters() {
        return new external_function_parameters(
            array(
                'lpt_id' => new external_value(PARAM_INT, 'Learning Plan ID'),
                'u_id' => new external_value(PARAM_INT, 'User ID'),
            )
        );
    }
    public static function add_user($lpt_id, $u_id) {
        global $DB, $USER;

        $params = self::validate_parameters(self::add_user_parameters(), array(
            'lpt_id' => $lpt_id,
            'u_id' => $u_id,
        ));

        if ($DB->record_exists('local_learningpath_users', array('lpt_id' => $params['lpt_id'], 'u_id' => $params['u_id']))) {
            return false;
        }

        $data = new stdClass();
        $data->lpt_id = $params['lpt_id'];
        $data->u_id = $params['u_id'];
        $data->assignee_id = $USER->id;
        $data->timecreated = time();

        $DB->insert_record('local_learningpath_users', $data);

        return true;
    }
    public static function add_user_returns() {
        return new external_value(PARAM_BOOL, 'True if success, false otherwise');
    }

    //search cohort
    public static function search_cohorts_parameters() {
        return new external_function_parameters(
            array(
                'search' => new external_value(PARAM_TEXT, 'Search string'),
            )
        );
    }
    public static function search_cohorts($search) {
        global $DB;
    
        $params = self::validate_parameters(self::search_cohorts_parameters(), array(
            'search' => $search,
        ));
    
        $searchTerm = '%' . $params['search'] . '%';
    
        $cohorts = $DB->get_records_sql(
                "SELECT id, name 
                    FROM {cohort} 
                WHERE name LIKE :name",
                array(
                    'name' => $searchTerm,
                )
            );
        $cohortsData = [];
        foreach($cohorts as $cohort) {
            $cohortsData[] = [
                'id' => $cohort->id,
                'name' => $cohort->name,
            ];
        }
    
        return array_values($cohortsData);
    }

    //get cohort
    public static function get_cohorts_parameters() {
        return new external_function_parameters(
            array()
        );
    }
    public static function get_cohorts() {
        global $DB;

        //$params = self::validate_parameters(self::get_cohorts_parameters());

        $cohorts = $DB->get_records('cohort', null, 'name', 'id, name');
        $cohortsData = [];
        foreach($cohorts as $cohort) {
            $cohortsData[] = [
                'id' => $cohort->id,
                'name' => $cohort->name,
            ];
        }

        return array_values($cohortsData);
    }
    public static function get_cohorts_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'Cohort ID'),
                    'name' => new external_value(PARAM_TEXT, 'Cohort Name'),
                )
            )
        );
    }

    //add cohort
    public static function add_cohort_parameters() {
        return new external_function_parameters(
            array(
                'lpt_id' => new external_value(PARAM_INT, 'Learning Plan ID'),
                'cohort_id' => new external_value(PARAM_INT, 'Cohort ID'),
            )
        );
    }
    public static function add_cohort($lpt_id, $cohort_id) {
        global $DB, $USER;

        $params = self::validate_parameters(self::add_cohort_parameters(), array(
            'lpt_id' => $lpt_id,
            'cohort_id' => $cohort_id,
        ));

        if ($DB->record_exists('local_learningpath_cohorts', array('lpt_id' => $params['lpt_id'], 'cohort_id' => $params['cohort_id']))) {
            return false;
        }

        $data = new stdClass();
        $data->lpt_id = $params['lpt_id'];
        $data->cohort_id = $params['cohort_id'];
        $data->assignee_id = $USER->id;
        $data->timecreated = time();

        $DB->insert_record('local_learningpath_cohorts', $data);

        return true;
    }
    public static function add_cohort_returns() {
        return new external_value(PARAM_BOOL, 'True if success, false otherwise');
    }
}
<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Get User Cohorts
 *
 * @package    Get User Cohorts
 * @copyright  2016 Christos Savva
 */
defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . "/externallib.php");

class local_wsgetusercohorts_external extends external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_user_cohorts_parameters() {
        return new external_function_parameters(
                array('userid' => new external_value(PARAM_INT, 'The ID of the user"', VALUE_DEFAULT))
        );
    }

    /**
     * Returns welcome message
     * @return string welcome message
     */
    public static function get_user_cohorts($userid = null) {
        global $USER, $DB;

        // Parameter validation.
        // REQUIRED!
        $params = self::validate_parameters(self::get_user_cohorts_parameters(),
                array('userid' => $userid));

        $context = context_user::instance($USER->id);

        self::validate_context($context);

        if (!has_capability('moodle/user:viewdetails', $context)) {
            throw new moodle_exception('cannotviewprofile');
        }

        $user = $DB->get_record('user', array('id' => $params['userid']));

        if ($user == null) {
            throw new \moodle_exception("User does not exist", 'get_user_cohorts');
        }

        $cohortsdb = $DB->get_records_sql('SELECT hm.cohortid, h.idnumber, h.name
                    FROM {cohort} h
                    JOIN {cohort_members} hm ON h.id = hm.cohortid
                    JOIN {user} u ON hm.userid = u.id
                    WHERE u.id=?', array($params['userid']));
        $returndata = array();
        $cohorts = array();
        foreach ($cohortsdb as $cohort) {
            $item = (array) $cohort;
            array_push($cohorts, $item);
        }
        $returndata['cohorts'] = $cohorts;
        return $returndata;
    }
    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function get_user_cohorts_returns() {
        return new external_single_structure(
            array(
                'cohorts' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'cohortid' => new external_value(PARAM_INT, 'Cohort ID'),
                            'idnumber' => new external_value(PARAM_RAW, 'Cohort id number'),
                            'name' => new external_value(PARAM_RAW, 'Cohort name'),
                        )
                    )
                )
            )
        );
    }

}

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
 * Summary renderable.
 *
 * @package    local\learningpath
 * @copyright  2016 Frédéric Massart - FMCorz.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_learningpath\output;
defined('MOODLE_INTERNAL') || die();

use local_wunderbyte_table\filters\types\datepicker;
use local_wunderbyte_table\filters\types\hierarchicalfilter;
use local_wunderbyte_table\filters\types\hourlist;
use local_wunderbyte_table\filters\types\intrange;
use local_wunderbyte_table\filters\types\standardfilter;
use local_wunderbyte_table\wunderbyte_table;
use local_learningpath\table\line_table;
use renderable;
use renderer_base;
use templatable;

/**
 * Summary renderable class.
 *
 * @package    local\learningpath
 * @copyright  2016 Frédéric Massart - FMCorz.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class line implements renderable, templatable {

    /**
     * An idstring for the table & spinner.
     *
     * @var string
     */
    public $idstring = 'line_table';

    /**
     * The encoded settings for the sql table.
     *
     * @var string
     */
    public $encodedtable = 'line_table';

    /**
     * Constructor.
     *
     */
    public function __construct() {
        \cache_helper::purge_by_event('changesinwunderbytetable');
    }

    public function render_line($lpt_id) {
        \cache_helper::purge_by_event('changesinwunderbytetable');
        $table = new line_table('line');

        $table->define_headers([
            get_string('id', 'local_learningpath'),
            get_string('name', 'local_learningpath'),
            get_string('sortorder', 'local_learningpath'),
            get_string('required', 'local_learningpath'),
            get_string('credit', 'local_learningpath'), 
            get_string('action')
        ]);
        $table->define_columns(['id', 'line', 'sortorder', 'required', 'credit', 'action']);

        $table->define_fulltextsearchcolumns(['line']);

        //$table->define_sortablecolumns(['learning_plan', 'startdate', 'enddate', 'published', 'credit']);

        $table->define_fulltextsearchcolumns(['learning_plan']);

        $table->addcheckboxes = false;

        $table->actionbuttons[] = [
            'label' => '+ Add Line',
            'class' => 'btn btn-primary',
            'href' => '#',
            'id' => -1,
            'formname' => 'local_learningpath\\form\\line_form',
            'nomodal' => false,
            'selectionmandatory' => true,
            'data' => [
                'id' => 'id',
                'lpt_id' => $lpt_id,
                'component' => 'local_learningpath',
                'titlestring' => 'addlinetitle',
                'submitbuttonstring' => 'close',
            ],
        ];

        //$table->sort_default_column = 'startdate';
        $table->sort_default_order = SORT_ASC;

        // Work out the sql for the table.
        $table->set_filter_sql('*', "(SELECT * FROM {local_learningpath_lines} ORDER BY id ASC) as s1", 'lpt_id='. $lpt_id .'', '');
        $table->cardsort = true;

        //$table->tabletemplate = 'local_wunderbyte_table/twtable_list';
        $table->tabletemplate = 'local_learningpath/table/twtable_list';
        //$table->define_cache('local_learningpath', 'index_table', 'cachedfulltable'); // This is the cache name, and the cache will be cleared when the table is updated.

        $table->pageable(true);
        $table->set_tableclass('table', 'table-striped');

        $table->infinitescroll = 20;
        $table->stickyheader = false;
        $table->showcountlabel = true;
        $table->showdownloadbutton = false;
        $table->showreloadbutton = true;
        $table->showrowcountselect = false;
        $table->filteronloadinactive = false;
        $table->hide_filter();

        list($idstring, $encodedtable, $html) = $table->lazyouthtml(20, true);
        return $html;
        //return $table->outhtml(20, true);
    }

    public function render_table($lpt_id = null){
        return [
            'table' => $this->render_line($lpt_id)
        ];
    }

    /**
     * Prepare data for use in a template
     *
     * @param renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output) {
        return [
            'table' => $this->render_line($lpt_id)
        ];
    }

}

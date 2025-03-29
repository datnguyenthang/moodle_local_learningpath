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
use local_learningpath\table\learningpath_table;
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
class learningpath implements renderable, templatable {

    /**
     * An idstring for the table & spinner.
     *
     * @var string
     */
    public $idstring;

    /**
     * The encoded settings for the sql table.
     *
     * @var string
     */
    public $encodedtable;

    /**
     * Constructor.
     *
     */
    public function __construct() {}

    public function render_learningpath() {
        $table = new learningpath_table('learningpath');

        $table->define_headers([
            get_string('id', 'local_learningpath'), 
            get_string('learningpathname', 'local_learningpath'), 
            get_string('startdate', 'local_learningpath'), 
            get_string('enddate', 'local_learningpath'), 
            get_string('published', 'local_learningpath'),
            get_string('credit', 'local_learningpath'), 
            get_string('action')]);

        $table->define_columns(['id', 'name', 'startdate', 'enddate', 'published', 'credit', 'action']);

        $table->define_sortablecolumns(['name', 'startdate', 'enddate', 'published', 'credit']);

        $table->define_fulltextsearchcolumns(['name']);
        $table->define_sortablecolumns(['startdate', 'enddate', 'published']);

        $table->addcheckboxes = false;

        $table->sort_default_column = 'startdate';
        $table->sort_default_order = SORT_ASC;

        $table->actionbuttons[] = [
            'label' => '+ New Path',
            'class' => 'btn btn-primary',
            'href' => '#',
            'formname' => 'local_learningpath\\form\\learningpath_form',
            'nomodal' => false,
            'selectionmandatory' => true,
            'id' => -1,
            'encodedtable' => 'encodedtable',
            'data' => [
                'title' => 'createnewlearningpath',
                'component' => 'local_learningpath',
                'titlestring' => 'editsortlinetitle',
                'submitbuttonstring' => 'submit',
            ]
        ];

        // Work out the sql for the table.
        $table->set_filter_sql('*', "(SELECT * FROM {local_learningpath} ORDER BY id ASC LIMIT 112) as s1", '1=1', '');

        $table->cardsort = true;

        $table->tabletemplate = 'local_wunderbyte_table/twtable_list';
        //$table->define_cache('local_learningpath', 'index_table', 'cachedfulltable');

        $table->pageable(true);
        $table->set_tableclass('table', 'table-striped');

        $table->infinitescroll = 20;
        $table->stickyheader = false;
        $table->showcountlabel = true;
        $table->showdownloadbutton = true;
        $table->showreloadbutton = true;
        $table->showrowcountselect = false;
        $table->filteronloadinactive = true;
        $table->hide_filter();

        list($idstring, $encodedtable, $html) = $table->lazyouthtml(20, true);
        return $html;

        //return $table->outhtml(20, true);
    }

    public function render_table(){
        return [
            'table' => $this->render_learningpath()
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
            'table' => $this->render_learningpath()
        ];
    }

}

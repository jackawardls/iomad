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

// this cannot call Moodle's config.php
// which makes things interesting

require_once('lib.php' );

// get the data
$data = new stdClass;
$data->notstarted = $_GET['notstarted'];
$data->inprogress = $_GET['inprogress'];
$data->completed = $_GET['completed'];

comprep::drawChart($data);
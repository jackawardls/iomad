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
 * Script to let a user edit the properties of a particular email template.
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/iomad/lib/blockpage.php');
require_once($CFG->dirroot . '/local/iomad/lib/company.php');
require_once($CFG->dirroot . '/blocks/iomad_company_admin/lib.php');
require_once($CFG->libdir . '/formslib.php');
require_once('lib.php');
require_once('config.php');

class template_edit_form extends moodleform {
    protected $isadding;
    protected $subject = '';
    protected $body = '';
    protected $templateid;
    protected $templaterecord;
    protected $companyid;

    public function __construct($actionurl, $isadding, $companyid, $templateid, $templaterecord) {
        $this->isadding = $isadding;
        $this->templateid = $templateid;
        $this->templaterecord = $templaterecord;
        $this->companyid = $companyid;
        parent::__construct($actionurl);
    }

    public function definition() {
        global $CFG, $PAGE, $DB;
        $context = context_system::instance();

        $mform =& $this->_form;

        $strrequired = get_string('required');

        $mform->addElement('hidden', 'templateid', $this->templateid);
        $mform->addElement('hidden', 'templatename', $this->templaterecord->name);
        $mform->addElement('hidden', 'companyid', $this->companyid);
        $mform->setType('templateid', PARAM_INT);
        $mform->setType('companyid', PARAM_INT);
        $mform->setType('templatename', PARAM_CLEAN);

        $company = new company($this->companyid);

        // Then show the fields about where this block appears.
        $mform->addElement('header', 'header', get_string('email_template', 'local_email', array(
            'name' => $this->templaterecord->name,
            'companyname' => $company->get_name()
        )));

        $mform->addElement('text', 'subject', get_string('subject', 'local_email'),
                            array('size' => 100));
        $mform->setType('subject', PARAM_NOTAGS);
        $mform->addRule('subject', $strrequired, 'required');

        $mform->addElement('textarea', 'body_editor', get_string('body', 'local_email'),
                           'wrap="virtual" rows="50" cols="100"');
        $mform->setType('body_editor', PARAM_NOTAGS);
        $mform->addRule('body_editor', $strrequired, 'required');

        $vars = EmailVars::vars();
        $options = "<option value=''>" . get_string('select_email_var', 'local_email') .
                   "</option>";
        foreach ($vars as $i) {
            $options .= "<option value='{{$i}}'>$i</option>";
        }

        $select = "<select class='emailvars' onchange='Iomad.onSelectEmailVar(this)'>
                 $options</select>";
        $html = "<div class='fitem'><div class='fitemtitle'></div><div class='felement'>
                 $select</div></div>";

        $mform->addElement('html', $html);

        global $PAGE;
        $PAGE->requires->js('/local/email/module.js');

        $submitlabel = null; // Default.
        if ($this->isadding) {
            $submitlabel = get_string('save_to_override_default_template', 'local_email');
            $mform->addElement('hidden', 'createnew', 1);
            $mform->setType('createnew', PARAM_INT);

        }
        $this->add_action_buttons(true, $submitlabel);
    }

    public function get_data() {
        $data = parent::get_data();
        if ($data) {
            if ($data->body_editor) {
                $data->body = $data->body_editor;
            }
        }

        return $data;
    }
}

$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);
$templateid = optional_param('templateid', 0, PARAM_INTEGER);
$templatename = optional_param('templatename', '', PARAM_NOTAGS);
$new = optional_param('createnew', 0, PARAM_INTEGER);

$context = context_system::instance();
require_login();
$PAGE->set_context($context);

$urlparams = array('templateid' => $templateid, 'templatename' => $templatename);
if ($returnurl) {
    $urlparams['returnurl'] = $returnurl;
}
$templatelist = new moodle_url('/local/email/template_list.php', $urlparams);

// Set the companyid to bypass the company select form if possible.
if (!empty($SESSION->currenteditingcompany)) {
    $companyid = $SESSION->currenteditingcompany;
} else if (!empty($USER->company)) {
    $companyid = company_user::companyid();
} else if (!has_capability('local/email:edit', context_system::instance())) {
    print_error('There has been a configuration error, please contact the site administrator');
} else {
    $blockpage->display_header();
    redirect(new moodle_url('/local/iomad_dashboard/index.php'),
             'Please select a company from the dropdown first');
}

if (!$new) {
    $isadding = false;

    if ($templateid) {
        $templaterecord = $DB->get_record('email_template', array('id' => $templateid),
                                                                  '*', MUST_EXIST);
        require_capability('local/email:edit', $context);
    } else {
        $isadding = true;
        $templateid = 0;
        $templaterecord = (object) $email[$templatename];
        $templaterecord->name = $templatename;
        require_capability('local/email:add', $context);
    }
} else {
    $isadding = true;
    $templateid = 0;
    $templaterecord = (object) $email[$templatename];
    $templaterecord->name = $templatename;

    require_capability('local/email:add', $context);
}

// Correct the navbar.
// Set the name for the page.
$linktext = get_string('edit_template', 'local_email');
// Set the url.
$linkurl = new moodle_url('/local/email/template_edit_form.php');
// Build the nav bar.
company_admin_fix_breadcrumb($PAGE, $linktext, $linkurl);

$blockpage = new blockpage($PAGE, $OUTPUT, 'email', 'local',
                           ($isadding ? 'addnewtemplate' : 'editatemplate'));
$blockpage->setup();

require_login(null, false); // Adds to $PAGE, creates $OUTPUT.
// Get the form data.

// Set up the form.
$mform = new template_edit_form($PAGE->url, $isadding, $companyid, $templateid, $templaterecord);
$templaterecord->body_editor = $templaterecord->body;
$mform->set_data($templaterecord);

if ($mform->is_cancelled()) {
    redirect($templatelist);

} else if ($data = $mform->get_data()) {
    $data->userid = $USER->id;

    if ($isadding) {
        $data->companyid = $companyid;
        $data->name = $templatename;
        $templateid = $DB->insert_record('email_template', $data);
        $data->id = $templateid;
    } else {
        $data->id = $templateid;
        $DB->update_record('email_template', $data);
    }

    redirect($templatelist);
}

$blockpage->display_header();

$mform->display();

echo $OUTPUT->footer();
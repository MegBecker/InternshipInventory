<?php

/**
 * Internship
 *
 * Forms relationship between a student, department, and agency.
 *
 * @author Robert Bost <bostrt at tux dot appstate dot edu>
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 * @package Intern
 */
PHPWS_Core::initModClass('intern', 'Email.php');
PHPWS_Core::initModClass('intern', 'Term.php');
PHPWS_Core::initModClass('intern', 'Major.php');
PHPWS_Core::initModClass('intern', 'Faculty.php');

class Internship {

    public $id;

    // Agency
    public $agency_id;

    // Department
    public $department_id;

    //public $faculty_supervisor_id;
    public $faculty_id;

    // Status info
    public $state;
    public $oied_certified;

    // Student data
    public $banner;
    public $first_name;
    public $middle_name;
    public $last_name;

    // Metaphones for fuzzy search
    public $first_name_meta;
    public $last_name_meta;

    // Academic info
    public $level;
    public $grad_prog;
    public $ugrad_major;
    public $gpa;
    public $campus;

    // Contact Info
    public $phone;
    public $email;

    // Student address
    public $student_address;
    public $student_city;
    public $student_state;
    public $student_zip;

    // Location data
    public $domestic;
    public $international;

    public $loc_address;
    public $loc_city;
    public $loc_state;
    public $loc_zip;
    public $loc_province;
    public $loc_country;

    // Term Info
    public $term;
    public $start_date = 0;
    public $end_date = 0;
    public $credits;
    public $avg_hours_week;
    public $paid;
    public $unpaid;
    public $stipend;
    public $pay_rate;

    // Course Info
    public $multi_part;
    public $secondary_part;
    public $course_subj;
    public $course_no;
    public $course_sect;
    public $course_title;

    // Corequisite Course Info
    // Course must be in the same subject, so there's no subject code
    public $corequisite_number;
    public $corequisite_section;

    // Type
    public $experience_type;

    /**
     * Constructs a new Internship object.
     */
    public function __construct(){

    }

    /**
     * @Override Model::getDb
     */
    public function getDb()
    {
        return new PHPWS_DB('intern_internship');
    }

    /**
     * Save model to database
     * @return - new ID of model.
     */
    public function save()
    {
        $db = $this->getDb();
        try {
            $result = $db->saveObject($this);
        } catch (Exception $e) {
            exit($e->getMessage());
        }

        if (PHPWS_Error::logIfError($result)) {
            throw new Exception($result->toString());
        }

        return $this->id;
    }

    /**
     * Delete model from database.
     */
    public function delete()
    {
        if (is_null($this->id) || !is_numeric($this->id))
            return false;

        $db = $this->getDb();
        $db->addWhere('id', $this->id);
        $result = $db->delete();

        if (PHPWS_Error::logIfError($result)) {
            throw new Exception($result->getMessage(), $result->getCode());
        }

        return true;
    }

    /**
     * @Override Model::getCSV
     * Get a CSV formatted for for this internship.
     */
    public function getCSV()
    {
        $csv = array();

        // Student data
        $csv['Banner ID']   = $this->banner;
        $csv['First Name']  = $this->first_name;
        $csv['Middle Name'] = $this->middle_name;
        $csv['Last Name']   = $this->last_name;

        // Academic Info
        $csv['Level']           = $this->getLevel();
        if($this->getLevel() == 'ugrad'){
            $csv['Undergrad Major'] = $this->getUgradMajor()->getName();
            $csv['Grduate Program'] = '';
        }else if($this->getLevel() == 'grad'){
            $csv['Undergrad Major'] = '';
            $csv['Graduate Program'] = $this->getGradProgram()->getName();
        }else{
            $csv['Undergrad Major'] = '';
            $csv['Grduate Program'] = '';
        }
        $csv['GPA']             = $this->getGpa();
        $csv['Campus']          = $this->getCampus();

        // Status Info
        $csv['Status']                 = $this->getWorkflowState()->getFriendlyName();
        $csv['OIED Certified']         = $this->isOiedCertified() == 1 ? 'Yes' : 'No';

        // Student Academic Info
        $csv['Phone #']     = $this->phone;
        $csv['Email']       = $this->email;

        // Student Address
        $csv['Student Address']        = $this->student_address;
        $csv['Student City']           = $this->student_city;
        $csv['Student State']          = $this->student_state;
        $csv['Student Zip']            = $this->student_zip;

        // Emergency Contact
        $csv['Emergency Contact Name']     = $this->getEmergencyContactName();
        $csv['Emergency Contact Relation'] = $this->getEmergencyContactRelation();
        $csv['Emergency Contact Phone']    = $this->getEmergencyContactPhoneNumber();

        // Internship Data
        $csv['Term']                   = Term::rawToRead($this->term, false);
        $csv['Start Date']             = $this->getStartDate(true);
        $csv['End Date']               = $this->getEndDate(true);
        $csv['Credits']                = $this->credits;
        $csv['Average Hours Per Week'] = $this->avg_hours_week;
        $csv['Paid']                   = $this->paid == 1 ? 'Yes' : 'No';
        $csv['Stipend']                = $this->stipend == 1 ? 'Yes' : 'No';
        $csv['Unpaid']                 = $this->unpaid == 1 ? 'Yes' : 'No';

        // Internship Type
        $csv['Experience Type']          = $this->getExperienceType();

        // Internship location data
        $csv['Domestic']               = $this->isDomestic() ? 'Yes' : 'No';
        $csv['International']          = $this->isInternational() ? 'Yes' : 'No';
        $csv['Location Address']       = $this->loc_address;
        $csv['Location City']          = $this->loc_city;
        $csv['Location State']         = $this->loc_state;
        $csv['Location Zip']           = $this->loc_zip;
        $csv['Province']               = $this->loc_province;
        $csv['Country']                = $this->loc_country;

        // Course Info
        $csv['Multi-part']             = $this->isMultipart() ? 'Yes' : 'No';
        $csv['Secondary Part']         = $this->isSecondaryPart() ? 'Yes' : 'No';
        $csv['Course Subject']         = $this->getSubject()->getName();
        $csv['Course Number']          = $this->course_no;
        $csv['Course Section']         = $this->course_sect;
        $csv['Course Title']           = $this->course_title;

        // Get external objects
        $a = $this->getAgency();
        $f = $this->getFaculty();
        $d = $this->getDepartment();
        $c = $this->getDocuments();

        // Merge data from other objects.
        $csv = array_merge($csv, $a->getCSV());

		if(count($c) > 0)
		{
			$csv['Document Uploaded']  = 'Yes';
		}
		else
		{
			$csv['Document Uploaded']  = 'No';
		}

        if ($f instanceof Faculty) {
            $csv = array_merge($csv, $f->getCSV());
        } else {
            $csv = array_merge($csv, Faculty::getEmptyCsvRow());
        }

        $csv = array_merge($csv, $d->getCSV());

        return $csv;
    }

    /**
     * Returns true if this internship is at the undergraduate level, false otherwise.
     *
     * @return boolean
     */
    public function isUndergraduate()
    {
        if($this->getLevel() == 'ugrad'){
            return true;
        }

        return false;
    }

    /**
     * Returns true if this internship is at the graduate level, false otherwise.
     * @return boolean
     */
    public function isGraduate()
    {
        if($this->getLevel() == 'grad'){
            return true;
        }

        return false;
    }

    /**
     * Get a Major object for the major of this student.
     */
    public function getUgradMajor()
    {
        PHPWS_Core::initModClass('intern', 'Major.php');
        if(!is_null($this->ugrad_major) && $this->ugrad_major != 0){
            return new Major($this->ugrad_major);
        }else{
            return null;
        }
    }

    /**
     * Get a GradProgram object for the graduate program of this student.
     */
    public function getGradProgram()
    {
        PHPWS_Core::initModClass('intern', 'GradProgram.php');
        if(!is_null($this->grad_prog) && $this->grad_prog != 0){
            return new GradProgram($this->grad_prog);
        }else{
            return null;
        }
    }

    /**
     * Get the Agency object associated with this internship.
     */
    public function getAgency()
    {
        PHPWS_Core::initModClass('intern', 'Agency.php');
        return new Agency($this->agency_id);
    }

    /**
     * Get the Faculty Supervisor object associated with this internship.
     *
     */
    public function getFaculty()
    {
        if(!isset($this->faculty_id)){
            return null;
        }

        PHPWS_Core::initModClass('intern', 'FacultyFactory.php');
        return FacultyFactory::getFacultyObjectById($this->faculty_id);
    }

	/**
	 * Get the Emergency Contact's First Name
	 */
	public function getEmergencyContactName()
	{
		PHPWS_Core::initModClass('intern', 'EmergencyContactFactory.php');
		$name = EmergencyContactFactory::getContactsForInternship($this);
		if(!empty($name))
		{
			 return $name[0]->getName();
		}
	}

		/**
	 * Get the Emergency Contact's Relationship
	 */
	public function getEmergencyContactRelation()
	{
		PHPWS_Core::initModClass('intern', 'EmergencyContactFactory.php');
		$relationship = EmergencyContactFactory::getContactsForInternship($this);
		if(!empty($relationship))
		{
			 return $relationship[0]->getRelation();
		}
	}

		/**
	 * Get the Emergency Contact's Phone Number
	 */
	public function getEmergencyContactPhoneNumber()
	{
		PHPWS_Core::initModClass('intern', 'EmergencyContactFactory.php');
		$phone = EmergencyContactFactory::getContactsForInternship($this);
		if(!empty($phone))
		{
			 return $phone[0]->getPhone();
		}
	}

    /**
     * Get the Department object associated with this internship.
     */
    public function getDepartment()
    {
        PHPWS_Core::initModClass('intern', 'Department.php');
        return new Department($this->department_id);
    }

    public function getSubject()
    {
        PHPWS_Core::initModClass('intern', 'Subject.php');
        return new Subject($this->course_subj);
    }

    /**
     * Get Document objects associated with this internship.
     */
    public function getDocuments()
    {
        PHPWS_Core::initModClass('intern', 'Intern_Document.php');
        $db = Intern_Document::getDB();
        $db->addWhere('internship_id', $this->id);
        return $db->getObjects('Intern_Document');
    }

    /**
     * Get the concatenated first name, middle name/initial, and last name.
     */
    public function getFullName()
    {
        $name = $this->first_name . ' ';
        // Middle name is not required. If no middle name as input then
        // this will not show the extra space for padding between middle and last name.
        $name .= (isset($this->middle_name) && $this->middle_name != '') ? $this->middle_name . ' ': null;
        $name .= $this->last_name;
        return $name;
    }

    /**
     * Get formatted dates.
     */
    public function getStartDate($formatted=false)
    {
        if (!$this->start_date) {
            return null;
        }
        if ($formatted) {
            return date('F j, Y', $this->start_date);
        } else {
            return $this->start_date;
        }
    }

    public function getEndDate($formatted=false)
    {
        if (!$this->end_date) {
            return null;
        }
        if ($formatted) {
            return date('F j, Y', $this->end_date);
        } else {
            return $this->end_date;
        }
    }

    /**
     * Is this internship domestic?
     *
     * @return bool True if this is a domestic internship, false otherwise.
     */
    public function isDomestic()
    {
        return $this->domestic;
    }

    /**
     * Is this internship International?
     *
     * @return bool True if this is an international internship, false otherwise.
     */
    public function isInternational()
    {
        return $this->international;
    }

    public function isOiedCertified()
    {
        if($this->oied_certified == 1){
            return true;
        }else{
            return false;
        }
    }

    public function isMultipart()
    {
        if($this->multi_part == 1){
            return true;
        }else{
            return false;
        }
    }

    public function isSecondaryPart()
    {
        if($this->secondary_part == 1){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Row tags for DBPager
     */
    public function getRowTags()
    {
        PHPWS_Core::initModClass('intern', 'Term.php');

        $tags = array();

        // Get objects associated with this internship.
        $a = $this->getAgency();
        $d = $this->getDepartment();

        // Student info.
        $tags['STUDENT_NAME'] = PHPWS_Text::moduleLink($this->getFullName(), 'intern', array('action' => 'edit_internship', 'internship_id' => $this->id));
        $tags['STUDENT_BANNER'] = PHPWS_Text::moduleLink($this->getBannerId(), 'intern', array('action' => 'edit_internship', 'internship_id' => $this->id));

        // Dept. info
        $tags['DEPT_NAME'] = PHPWS_Text::moduleLink($d->name, 'intern', array('action' => 'edit_internship', 'internship_id' => $this->id));

        // Faculty info.
        if(isset($this->faculty_id)){
            $f = $this->getFaculty();
            $facultyName = $f->getFullName();
            $tags['FACULTY_NAME'] = PHPWS_Text::moduleLink($f->getFullName(), 'intern', array('action' => 'edit_internship', 'internship_id' => $this->id));
        }else{
            // Makes this cell in the table a clickable link, even if there's no faculty name
            $tags['FACULTY_NAME'] = PHPWS_Text::moduleLink('&nbsp;', 'intern', array('action' => 'edit_internship', 'internship_id' => $this->id));
        }

        $tags['TERM'] = PHPWS_Text::moduleLink(Term::rawToRead($this->term), 'intern', array('action' => 'edit_internship', 'internship_id' => $this->id));

        $tags['WORKFLOW_STATE'] = PHPWS_Text::moduleLink($this->getWorkflowState()->getFriendlyName(), 'intern', array('action' => 'edit_internship', 'internship_id' => $this->id));

        //$tags['EDIT'] = PHPWS_Text::moduleLink('Edit', 'intern', array('action' => 'edit_internship', 'internship_id' => $this->id));
        //$tags['PDF'] = PHPWS_Text::moduleLink('Generate Contract', 'intern', array('action' => 'pdf', 'id' => $this->id));

        return $tags;
    }

    public function getLocCountry()
    {
        if (!$this->loc_country) {
            return 'United States';
        }
        return $this->loc_country;
    }

    /*****************************
     * Accessor / Mutator Methods
    */

    /**
     * Returns the database id of this internship.
     *
     * @return int
     */
    public function getId(){
        return $this->id;
    }

    /**
     * Returns the Banner ID of this student.
     *
     * @return string Banner ID
     */
    public function getBannerId(){
        return $this->banner;
    }

    public function getEmailAddress(){
        return $this->email;
    }

    public function getFacultyId()
    {
        return $this->faculty_id;
    }

	public function getStreetAddress(){
		return $this->loc_address;
	}

    /**
     * Get the domestic looking address of agency.
     */
    public function getLocationAddress()
    {
        $add = array();

        if (!empty($this->loc_address)) {
            $add[] = $this->loc_address . ',';
        }
        if (!empty($this->loc_city)) {
            $add[] = $this->loc_city . ',';
        }
        if(!empty($this->loc_state)){
            $add[] = $this->loc_state;
        }
        if (!empty($this->loc_zip)) {
            $add[] = $this->loc_zip;
        }

        if(!empty($this->loc_province)){
            $add[] = $this->loc_province . ', ';
        }

        if(!empty($this->loc_country)){
            $add[] = $this->loc_country;
        }

        return implode(' ', $add);
    }

    /**
     * Returns the Department's database id
     * @return integer department id
     */
    public function getDepartmentId()
    {
        return $this->department_id;
    }

    /**
     * Returns the WorkflowState name for this internshio's current state/status.
     * Can be null if no state has been set yet.
     *
     * @return string
     */
    public function getStateName()
    {
        return $this->state;
    }

    /**
     * Sets the WorkflowState of this internship.
     *
     * @param WorkflowState $state
     */
    public function setState(WorkflowState $state){
        $this->state = $state->getName();
    }

    /**
     * Returns the WorkflowState object represeting this internship's current state/status.
     * Returns null if no state has been set yet.
     *
     * @return WorkflowState
     */
    public function getWorkflowState()
    {
        $stateName = $this->getStateName();

        if(is_null($stateName)){
            return null;
        }

        PHPWS_Core::initModClass('intern', 'WorkflowStateFactory.php');
        return WorkflowStateFactory::getState($stateName);
    }

    /**
     * Returns the campus on which this internship is based
     *
     * Valid values are: 'main_campus', 'distance_ed'
     *
     * @return String campus name
     */
    public function getCampus()
    {
        return $this->campus;
    }

    /**
     * Returns true if this is a Distance Ed internship, false otherwise.
     *
     * @return boolean
     */
    public function isDistanceEd()
    {
        if($this->getCampus() == 'distance_ed'){
            return true;
        }

        return false;
    }


    /**
     * Calculates and sets the metaphone value for this student's first name.
     *
     * @param string $firstName
     */
    public function setFirstNameMetaphone($firstName){
        $this->first_name_meta = metaphone($firstName);
    }

    /**
     * Calculates and sets the metaphone value for this student's last name.
     *
     * @param string $lastName
     */
    public function setLastNameMetaphone($lastName){
        $this->last_name_meta = metaphone($lastName);
    }

    /**
     * Returns this student's level ('grad', or 'undergrad')
     *
     * @return string
     */
    public function getLevel(){
        return $this->level;
    }

    public function getGpa(){
        return $this->gpa;
    }

    public function getPhoneNumber(){
        return $this->phone;
    }

    public function getStudentAddress()
    {
        $studentAddress = "";
        if(!empty($this->student_address)){
            $studentAddress .= ($this->student_address . ", ");
        }
        if(!empty($this->student_city)){
            $studentAddress .= ($this->student_city . ", ");
        }
        if(!empty($this->student_state) && $this->student_state != '-1'){
            $studentAddress .= ($this->student_state . " ");
        }
        if(!empty($this->student_zip)){
            $studentAddress .= $this->student_zip;
        }

        return $studentAddress;
    }

    /**
     * Returns this internship's term
     *
     * @return int
     */
    public function getTerm(){
        return $this->term;
    }

    public function getCourseNumber(){
        return $this->course_no;
    }

    public function getCourseSection(){
        return $this->course_sect;
    }

    public function getCourseTitle(){
        return $this->course_title;
    }

    public function getCreditHours(){
        return $this->credits;
    }

    public function getCorequisiteNum(){
        return $this->corequisite_number;
    }

    public function getCorequisiteSection(){
        return $this->corequisite_section;
    }

    public function getAvgHoursPerWeek(){
        return $this->avg_hours_week;
    }

    public function isPaid(){
        if($this->paid == 1){
            return true;
        }

        return false;
    }

    public function isUnPaid(){
        if($this->unpaid == 1){
            return true;
        }

        return false;
    }

    public function getExperienceType(){
        return $this->experience_type;
    }

    public function setExperienceType($type){
        $this->experience_type = $type;
    }

    /***********************
     * Static Methods
    ***********************/
    public static function getTypesAssoc()
    {
        return array('internship'       => 'Internship',
                'student_teaching' => 'Student Teaching',
                'practicum'        => 'Practicum',
                'clinical'         => 'Clinical');
    }
}

?>

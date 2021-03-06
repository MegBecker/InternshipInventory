<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/gpl-3.0.html
 */
class State {

    public $abbr;
    public $fullname;
    public $active;

    public function __construct($abbr)
    {
        $db = new PHPWS_DB('intern_state');
        $db->addWhere('abbr', $abbr);
        return $db->loadObject($this);
    }

    public function save()
    {
        $db = new PHPWS_DB('intern_state');
        $db->addWhere('abbr', $this->abbr);
        return $db->saveObject($this);
    }
    
    public function setActive($active)
    {
        $this->active = (bool)$active;
    }

    public static function getAllowedStates()
    {
        $db = new PHPWS_DB('intern_state');
        $db->addWhere('active', 1);
        $db->addColumn('abbr');
        $db->addColumn('full_name');
        $db->setIndexBy('abbr');
        // get backwards because we flip it
        $db->addOrder('full_name desc');
        $states = $db->select('col');
        if (empty($states)) {
            NQ::simple('intern', INTERN_ERROR, 'The list of allowed US states for internship locations has not been configured. Please use the administrative options to <a href="index.php?module=intern&action=edit_states">add allowed states.</a>');
            NQ::close();
            PHPWS_Core::goBack();
        }
        $states[-1] = 'Select a state';
        $states = array_reverse($states, true);
        
        return $states;
    }
    
    /* http://www.bytemycode.com/snippets/snippet/454/ */    
    public static $UNITED_STATES = array(-1 => 'Select State',
            'AL' => "Alabama",
            'AK' => "Alaska",
            'AZ' => "Arizona",
            'AR' => "Arkansas",
            'CA' => "California",
            'CO' => "Colorado",
            'CT' => "Connecticut",
            'DE' => "Delaware",
            'DC' => "District Of Columbia",
            'FL' => "Florida",
            'GA' => "Georgia",
            'HI' => "Hawaii",
            'ID' => "Idaho",
            'IL' => "Illinois",
            'IN' => "Indiana",
            'IA' => "Iowa",
            'KS' => "Kansas",
            'KY' => "Kentucky",
            'LA' => "Louisiana",
            'ME' => "Maine",
            'MD' => "Maryland",
            'MA' => "Massachusetts",
            'MI' => "Michigan",
            'MN' => "Minnesota",
            'MS' => "Mississippi",
            'MO' => "Missouri",
            'MT' => "Montana",
            'NE' => "Nebraska",
            'NV' => "Nevada",
            'NH' => "New Hampshire",
            'NJ' => "New Jersey",
            'NM' => "New Mexico",
            'NY' => "New York",
            'NC' => "North Carolina",
            'ND' => "North Dakota",
            'OH' => "Ohio",
            'OK' => "Oklahoma",
            'OR' => "Oregon",
            'PA' => "Pennsylvania",
            'RI' => "Rhode Island",
            'SC' => "South Carolina",
            'SD' => "South Dakota",
            'TN' => "Tennessee",
            'TX' => "Texas",
            'UT' => "Utah",
            'VT' => "Vermont",
            'VA' => "Virginia",
            'WA' => "Washington",
            'WV' => "West Virginia",
            'WI' => "Wisconsin",
            'WY' => "Wyoming");
    
}

?>

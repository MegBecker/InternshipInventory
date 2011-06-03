<?php
/**
 * Editable
 *
 * This abstract class makes things easier for building
 * the UI used for editing majors, grad programs, and 
 * departments. Anything else that needs to be 
 * hidden, renamed, or deleted can extend this abstract class
 * and be easily plugged into the javascript (edit).
 *
 *@author Robert Bost <bostrt at tux dot appstate dot edu>
 */
PHPWS_Core::initModClass('intern', 'Mode.php');
abstract class Editable extends Model
{
    /**
     * This should return a string that corresponds
     * the the case statement in index.php
     * Ex. Major implements Editable and it's 
     *     getEditAction method returns 'edit_major'.
     */
    abstract static function getEditAction();

    /**
     * Get the name of the permission needed to edit the item.
     */
    abstract static function getEditPermission();

    /** TODO: need abstract method for getDeletePermission **/

    /**
     * Rename the Editable item with ID $id to $newName.
     */
    public function rename($newName)
    {
        /* Permission check */
        if(!Current_User::allow('intern', $this->getEditPermission())){
            return NQ::simple('intern', INTERN_ERROR, 'You do not have permission to rename this.');
        }

        /* Must be valid name */
        $newName = trim($newName);
        if($newName == ''){
            return NQ::simple('intern', INTERN_WARNING, 'No name was given. Nothing were changed.');
        }
       
        /* Check ID */
        if($this->id == 0){
            // Editable wasn't loaded correctly
            if(isset($_REQUEST['ajax'])){
                NQ::simple('intern', INTERN_ERROR, "Error occurred while loading information from database.");
                NQ::close();
                echo true;
                exit;
            }
            NQ::simple('intern', INTERN_ERROR, "Error occurred while loading information from database.");
            return;
        }
        /* Keep old name around for NQ */
        $old = $this->name;
        try{
            /* Save and notify */
            $this->name = $newName;
            $this->save();
            if(isset($_REQUEST['ajax'])){
                NQ::simple('intern', INTERN_SUCCESS, "<i>$old</i> renamed to <i>$newName</i>");
                NQ::close();
                echo true;
                exit;
            }
            return NQ::simple('intern', INTERN_SUCCESS, "<i>$old</i> renamed to <i>$newName</i>");
        }catch(Exception $e){
            if(isset($_REQUEST['ajax'])){
                NQ::simple('intern', INTERN_ERROR, $e->getMessage());
                NQ::close();
                echo false;
                exit;
            }
            return NQ::simple('intern', INTERN_ERROR, $e->getMessage());
        }
    }

    /**
     * Set an editable item to hidden/visible depending on parameter.
     */
    public function hide($hide=true)
    {
        /* Permission check */
        if(!Current_User::allow('intern', $this->getEditPermission())){
            return NQ::simple('intern', INTERN_ERROR, 'You do not have permission to hide that.');
        }
        
        if($this->id == 0 || !is_numeric($this->id)){
            // Program wasn't loaded correctly
            NQ::simple('intern', INTERN_ERROR, "Error occurred while loading information from database.");
            return;
        }

        // Set the item's hidden flag in DB.
        if($hide){
            $this->hidden = 1;
        }else{
            $this->hidden = 0;
        }

        try{
            $this->save();
            if($this->hidden == 1){
                NQ::simple('intern', INTERN_SUCCESS, "<i>$this->name</i> is now hidden.");
            }
            else{
                NQ::simple('intern', INTERN_SUCCESS, "<i>$this->name</i> is now visible.");
            }
        }catch(Exception $e){
            return NQ::simple('intern', INTERN_ERROR, $e->getMessage());
        }
    }

    /**
     * Delete an editable item from database.
     */
    public function del()
    {
        /** TODO: Permission check **/

        if($this->id == 0){
            // Item wasn't loaded correctly
            NQ::simple('intern', INTERN_ERROR, "Error occurred while loading information from database.");
            return;
        }

        $name = $this->getName();
        
        try{
            // Try to delete item
            if(!$this->delete()){
                // Something bad happend. This should have been caught in the check above...
                NQ::simple('intern', INTERN_SUCCESS, "Error occurred removing <i>$name</i> from database.");
                return;
            }
            // Item deleted successfully.
            NQ::simple('intern', INTERN_SUCCESS, "Deleted <i>$name</i>");
        }catch(Exception $e){
            if($e->getCode() == DB_ERROR_CONSTRAINT){
                // TODO: Ask user if they want to force the delete. Will set student's major/etc to null.
                NQ::simple('intern', INTERN_ERROR, "One or more students have $name as their Major, Grad Program, or Department. Cannot delete");
                return;
            }

            NQ::simple('intern', INTERN_ERROR, $e->getMessage());
            return;
        }
    }
}

?>
<?php
namespace com;
class WriteFile{
    public function write($filepath, $contents,$smarty)
    {
        $_error_reporting = error_reporting();
        error_reporting($_error_reporting & ~E_NOTICE & ~E_WARNING);
        if ($smarty->_file_perms !== null) {
            $old_umask = umask(0);
        }

        $_dirpath = dirname($filepath);
        // if subdirs, create dir structure
        if ($_dirpath !== '.' && !file_exists($_dirpath)) {
            mkdir($_dirpath, $smarty->_dir_perms === null ? 0777 : $smarty->_dir_perms, true);
        }

        // write to tmp file, then move to overt file lock race condition
        $_tmp_file = $_dirpath . DS . uniqid('wrt');
        if (!file_put_contents($_tmp_file, $contents)) {
            error_reporting($_error_reporting);
            throw new SmartyException("unable to write file {$_tmp_file}");
            return false;
        }

        // remove original file
        @unlink($filepath);

        // rename tmp file
        $success = rename($_tmp_file, $filepath);
        if (!$success) {
            error_reporting($_error_reporting);
            throw new SmartyException("unable to write file {$filepath}");
            return false;
        }

        if ($smarty->_file_perms !== null) {
            // set file permissions
            chmod($filepath, $smarty->_file_perms);
            umask($old_umask);
        }
        error_reporting($_error_reporting);
        return true;
    }

}

?>
<?php
/**
 * appends text to a file
 *
 * @param string $file is the file
 * @param string $data is the data
 * @param string $delimiter delimits the string (i.e. "\n") - defaults to empty string
 * @return int the number of bytes written
 */
function file_append($file, $data, $delimiter = '') {
    return file_write($file, $data . $delimiter, FILE_APPEND);
}

/**
 * closes a file pointer and destroys the pointer
 *
 * @param resource $file is the file handler
 */
function file_close(&$file) {
    if (!fclose($file)) {
        pfunc_error('Failed to close file ' . spy($file) . '.');
    }

    destroy($file);
}

/**
 * wrapper for file_get_contents
 *
 * @param string $file is the file name
 * @param mixed $default is the default to return if file doesn't exist or is not readable
 * @return mixed either the contents of the file or the default if file dont exist
 */
function file_read($file, $default = null) {
    if (!is_readable($file) && func_num_args() > 1) {
        return $default;
    }

    if (!is_string($data = file_get_contents($file))) {
        pfunc_error('Failed to read ' . spy($file) . '.');
    }

    return $data;
}

/**
 * a wrapper for file_put_contents
 *
 * @param string $file is the name of the file
 * @param string $data is the data to write to the file
 * @param int $flags is the flags
 * @return int the number of bytes written
 */
function file_write($file, $data, $flags = 0) {
    if (($bytes = file_put_contents($file, $data, $flags)) !== strlen($data)) {
        pfunc_error('Failed to write ' . spy($data) . ' to ' . spy($file) . '.');
    }

    return $bytes;
}

/**
 * deletes a directory - like rmdir except deletes whether it's empty or not
 *
 * @param string $dir is the directory
 */
function delete_dir($dir) {
    foreach (array_keys(scan_dir($dir)) as $file) {
        if (is_dir($file)) {
            delete_dir($file);
        } else {
            rm($file);
        }
    }

    rmdir($dir);
}

/**
 * creates a temporary directory
 *
 * @return string the path
 */
function tmpdir($delete_when_done = true) {
    do {
        $tmpdir = sys_get_temp_dir() . '/' . uniqid();
    } while (file_exists($tmpdir));

    mkdir($tmpdir);

    if (!$delete_when_done) {
        return $tmpdir;
    }

    register_shutdown_function(function () use ($tmpdir) {
        delete_dir($tmpdir);
    });

    return $tmpdir;
}

/**
 * copy a directory
 *
 * @param string $src is the source dir
 * @param string $dst is the destination dir
 */
function copy_dir($src, $dst) {
    $dir = opendir($src);

    mkdir($dst);

    while (($file = readdir($dir)) !== false) {
        if ($file === '.' || $file === '..') {
            continue;
        }

        if (is_dir($src . '/' . $file) ) {
            copy_dir($src . '/' . $file,$dst . '/' . $file);
        } else {
            copy($src . '/' . $file, $dst . '/' . $file);
        }
    }

    closedir($dir);
}

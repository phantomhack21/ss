<?php

/**
 * Simple, file-based logging utility.
 */
class Log {
    // Define the path to the log file. You should typically
    // set this path outside of the web-accessible directory.
    const LOG_FILE = 'application_logger/logs_dir/application.log';
    
    // Define standard log levels for filtering and clarity
    const LEVEL_INFO    = 'INFO';
    const LEVEL_WARNING = 'WARNING';
    const LEVEL_ERROR   = 'ERROR';
    const LEVEL_DEBUG   = 'DEBUG';

    /**
     * Writes a message to the log file.
     * * @param string $level The severity level (e.g., 'INFO', 'ERROR').
     * @param string $message The message content to log.
     * @return bool True on success, false on failure.
     */
    public static function add(string $level, string $message): bool {
        // 1. Validate the log file path
        if (empty(self::LOG_FILE)) {
            // In a real application, you might use error_log() here
            // to report that the logging system is misconfigured.
            return false;
        }

        // 2. Format the log entry
        $timestamp = date('Y-m-d H:i:s');
        $log_entry = sprintf("[%s] [%s] %s\n", $timestamp, $level, $message);
        
        // 3. Write the entry to the log file
        // FILE_APPEND: ensures the content is added to the end of the file.
        // LOCK_EX: ensures that no other process writes to the file simultaneously.
        $result = file_put_contents(self::LOG_FILE, $log_entry, FILE_APPEND | LOCK_EX);

        // file_put_contents returns the number of bytes written, or false on failure.
        return ($result !== false);
    }
}


echo "Logging examples complete. Check the file: " . Log::LOG_FILE . "\n";

?>
<?php

/**
 * @package WP Static HTML Output
 *
 * Copyright (c) 2011 Leon Stafford
 */
use  Aws\S3\S3Client ;
class StaticHtmlOutput_S3
{
    protected  $_key ;
    protected  $_secret ;
    protected  $_region ;
    protected  $_bucket ;
    protected  $_remotePath ;
    protected  $_uploadsPath ;
    protected  $_exportFileList ;
    protected  $_archiveName ;
    public function __construct(
        $key,
        $secret,
        $region,
        $bucket,
        $remotePath,
        $uploadsPath
    )
    {
        $this->_key = $key;
        $this->_secret = $secret;
        $this->_region = $region;
        $this->_bucket = $bucket;
        $this->_remotePath = $remotePath;
        $this->_exportFileList = $uploadsPath . '/WP-STATIC-EXPORT-S3-FILES-TO-EXPORT';
        $archiveDir = file_get_contents( $uploadsPath . '/WP-STATIC-CURRENT-ARCHIVE' );
        $this->_archiveName = rtrim( $archiveDir, '/' );
    }
    
    public function clear_file_list()
    {
        $f = @fopen( $this->_exportFileList, "r+" );
        
        if ( $f !== false ) {
            ftruncate( $f, 0 );
            fclose( $f );
        }
        
        $f = @fopen( $this->_globHashAndPathList, "r+" );
        
        if ( $f !== false ) {
            ftruncate( $f, 0 );
            fclose( $f );
        }
    
    }
    
    // TODO: move this into a parent class as identical to bunny and probably others
    public function create_s3_deployment_list( $dir, $archiveName, $remotePath )
    {
        $files = scandir( $dir );
        foreach ( $files as $item ) {
            if ( $item != '.' && $item != '..' && $item != '.git' ) {
                
                if ( is_dir( $dir . '/' . $item ) ) {
                    $this->create_s3_deployment_list( $dir . '/' . $item, $archiveName, $remotePath );
                } else {
                    
                    if ( is_file( $dir . '/' . $item ) ) {
                        $subdir = str_replace( '/wp-admin/admin-ajax.php', '', $_SERVER['REQUEST_URI'] );
                        $subdir = ltrim( $subdir, '/' );
                        $clean_dir = str_replace( $archiveName . '/', '', $dir . '/' );
                        $clean_dir = str_replace( $subdir, '', $clean_dir );
                        $targetPath = $remotePath . $clean_dir;
                        $targetPath = ltrim( $targetPath, '/' );
                        $export_line = $dir . '/' . $item . ',' . $targetPath . "\n";
                        file_put_contents( $this->_exportFileList, $export_line, FILE_APPEND | LOCK_EX );
                    }
                
                }
            
            }
        }
    }
    
    // TODO: move this into a parent class as identical to bunny and probably others
    public function prepare_deployment()
    {
    }
    
    public function get_item_to_export()
    {
        $f = fopen( $this->_exportFileList, 'r' );
        $line = fgets( $f );
        fclose( $f );
        // TODO reduce the 2 file reads here, this one is just trimming the first line
        $contents = file( $this->_exportFileList, FILE_IGNORE_NEW_LINES );
        array_shift( $contents );
        file_put_contents( $this->_exportFileList, implode( "\r\n", $contents ) );
        return $line;
    }
    
    public function get_remaining_items_count()
    {
        $contents = file( $this->_exportFileList, FILE_IGNORE_NEW_LINES );
        // return the amount left if another item is taken
        #return count($contents) - 1;
        return count( $contents );
    }
    
    public function s3_put_object(
        $targetPath,
        $fileContents,
        $contentType = "text/plain",
        $pluginInstance
    )
    {
    }
    
    public function transfer_files()
    {
    }

}
<?php

namespace App\CloudProviders;

class CloudInitBash {

        private $command = [];
        private $basedir;
        private $ls = PHP_EOL;
        private $le = ';';

        /**
         * Instantiates a the class
         */
        public function __construct(string $basedir) {
                $this->basedir = $basedir . '/';
                array_push($this->command, '#!/bin/bash');
        }


        /**
         * Sets the parameter value as per the provided value in the
         * .env file present inside the Laravel application folder
         * If the parameter is not present, it will be added.
         */
        public function env($param, $value)
        {
                return $this->parameter($this->basedir . '.env', $param, $value);
        }


        /**
         * Runs a Laravel artisan command
         */
        public function artisan ($artisanCommands) {
                if (is_array($artisanCommands)) {
                        foreach($artisanCommands as $artisanCommand) {
                                $this->command( '/usr/bin/php '. $this->basedir . 'artisan ' . $artisanCommand);
                        }
                } else {
                        $this->command( '/usr/bin/php ' . $this->basedir . 'artisan ' . $artisanCommands);
                }
        }



        /**
         * If the parameter is defined in the file, it will update the parameter
         * with the given value. If the parameter is not present in the file,
         * it will be inserted with the given value. The line must begin
         * with the name of this parameter. This uses 'sed' command.
         */
        public function parameter(string $absoluteFilePath, string $parameter, string $value) 
        {
                $isParamThere = sprintf( "grep -q '^%s' %s", $parameter, $absoluteFilePath);
                $updateParam  = sprintf( "sed -i 's/^%s.*/%s=%s/' %s", $parameter, $parameter, $value, $absoluteFilePath);
                $insertParam  = sprintf( "echo '%s=%s' >> %s", $parameter, $value, $absoluteFilePath);

                $command = $isParamThere . ' && ' . $updateParam . ' || ' . $insertParam;
                return $this->command($command);
        }

     


        /**
         * Adds a single line bash command to the execution list
         */
        public function command(string $command)
        {
                return array_push($this->command, $command . $this->le);
        }


        /**
         * Returns the bash script for cloud init.
         */
        public function getCloudInitScript() : string
        {
                return implode($this->ls, $this->command);
        }

        // public function getCommandArray()
        // {
        //         return $this->command;
        // }

        public function save(string $path = '/tmp', string $filename = 'init-script.sh')
        {
                return file_put_contents($path . '/' . $filename, $this->getCloudInitScript());
        }
}
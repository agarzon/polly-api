<?php
/**
 * Create voices from text using Polly API from AWS
 *
 * PHP version 7
 *
 * @category  PHP
 * @package   PollyApi
 * @author    Alexander Garzon <agarzon@php.net>
 * @copyright 2018 Alexander Garzon
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 * @link      https://github.com/agarzon
 */

require 'vendor/autoload.php';

use Aws\Polly\PollyClient;
use Cocur\Slugify\Slugify;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;

/**
 * Main Class
 */
class PollyApi
{
    // Available voices for EN-US: Salli, Joanna, Ivy, Kendra, Kimberly, Matthew, Justin, Joey
    public $voices = ['Matthew', 'Joey', 'Kimberly'];

    private $PollyClient;

    private $FileSystem;

    private $Slugify;

    /**
     * Constructor
     */
    public function __construct()
    {

        try {
            set_time_limit(0);

            $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
            $dotenv->load();

            $this->PollyClient = new PollyClient(
                [
                    'version' => 'latest',
                    'region' => $_ENV['AWS_REGION'],
                    'credentials' => [
                        'key' => $_ENV['AWS_KEY'],
                        'secret' => $_ENV['AWS_SECRET'],
                    ],
                ]
            );

            $adapter = new Local(__DIR__);
            $this->FileSystem = new Filesystem($adapter);
            $this->Slugify = new Slugify();

            $this->loop();
        } catch (\Throwable $th) {
            echo 'ERROR: ' . $th->getMessage();
        }
    }

    /**
     * Generate MP3 files
     *
     * @param string $text  the speeched text
     * @param string $voice selected voice
     *
     * @return void
     */
    private function generateMp3($text, $voice = 'Matthew')
    {

        $result = $this->PollyClient->synthesizeSpeech(
            [
                'OutputFormat' => 'mp3',
                'Text' => $text,
                'TextType' => 'text',
                'VoiceId' => $voice,
            ]
        );

        return $result->get('AudioStream')->getContents();
    }

    /**
     * Main process
     *
     * @return void
     */
    public function loop()
    {
        try {
            // Get all files from texts folder
            $files = $this->FileSystem->listContents('/texts/');

            foreach ($this->voices as $voice) {
                // Creates for voice folder
                $this->FileSystem->createDir('/output/' . $voice);

                foreach ($files as $file) {
                    // Create directories based in file name
                    $destDirectory = '/output/' . $voice . '/' . $file['filename'];
                    $this->FileSystem->createDir($destDirectory);
                    $fileContent = $this->FileSystem->read($file['path']);
                    $lines = explode(PHP_EOL, $fileContent);
                    // Reads each line, create the mp3 and save the content
                    foreach ($lines as $line) {
                        $fileName = $this->Slugify->slugify($line) . '.mp3';
                        $stream = $this->generateMp3($line, $voice);
                        $this->FileSystem->write($destDirectory . '/' . $fileName, $stream);
                    }
                }
            }
        } catch (\Throwable $th) {
            echo 'ERROR: ' . $th->getMessage();
        }
    }
}

new PollyApi;

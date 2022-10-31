<?php

namespace Hoiast\AluraDownloader;

use Hoiast\AluraDownloader\StringSanitizer;
use Hoiast\AluraDownloader\HttpClient;

class AluraDownloader
{
    private HttpClient $httpClient;
    private StringSanitizer $stringSanitizer;
    private string $outputDirRoot;
    private array $qualityOrder;

    /**
     * Initialize the AluraDownloader class.
     * @param string $username Alura username
     * @param string $password Alura password
     * @param string $outputDirRoot The root directory where the videos will be saved.
     * @param string[] $qualityOrder The order of the video quality to be downloaded.
     */

    public function __construct(
        string $username,
        string $password,
        string $outputDirRoot = "./downloads/",
        array $qualityOrder = ['hd', 'sd']
    ) {
        $this->httpClient = new HttpClient($username, $password);
        $this->stringSanitizer = new StringSanitizer();
        $this->setOutputDirRoot($outputDirRoot);
        $this->setQualityOrder($qualityOrder);
    }

    /**
     * Download all video lessons from each course url.
     * @param string[] $courseURLs
     */
    public function downloadCourses(array $courseURLs): void
    {
        echo "AluraDownloader will download video classes for the following courses: " . PHP_EOL;
        foreach ($courseURLs as $courseURL) {
            echo "- $courseURL" . PHP_EOL;
        }

        // Initialize async downloads (Promises)
        foreach ($courseURLs as $courseURL) {
            $this->downloadCourse($courseURL);
        }

        // Wait for the requests to complete, even if some of them fail
        $this->httpClient->awaitPromises();

        echo "All downloads are complete!" . PHP_EOL;
    }

    private function downloadCourse(string $courseURL): void
    {
        // Get course info
        $courseInfo = $this->getCourseInfo($courseURL);

        // Create course output directory
        $courseTitle = $this->stringSanitizer->sanitize($courseInfo['name']);
        $courseDirectory = $this->outputDirRoot . $courseTitle;
        $this->createDirectory($courseDirectory);

        // Iterate over course sections
        foreach ($courseInfo['sections'] as $section) {
            $this->downloadSection($section, $courseDirectory, $courseInfo['slug']);
        }
    }

    private function getCourseInfo(string $courseURL): array
    {
        $courseSlug = explode('course/', $courseURL)[1];
        echo "Course slug: $courseSlug" . PHP_EOL;
        $data = $this->httpClient->get("/mobile/v2/course/$courseSlug");
        return $data;
    }

    private function downloadSection(
        array $section,
        string $courseDirectory,
        string $courseSlug
    ): void {
        // Create section output directory
        $sectionTitle = $section["position"] . "-" . $this->stringSanitizer->sanitize($section['titulo']);
        $sectionDirectory = $courseDirectory . '/' . $sectionTitle;
        $this->createDirectory($sectionDirectory);

        // Iterate over section lessons containing videos
        foreach ($section['videos'] as $video) {
            $this->downloadVideo($video, $sectionDirectory, $sectionTitle, $courseSlug);
        }
    }

    private function downloadVideo(
        array $video,
        string $sectionDirectory,
        string $sectionTitle,
        string $courseSlug
    ): void {
        // Prepare output file name
        $videoTitle = $video['position'] . "-" . $this->stringSanitizer->sanitize($video['nome']);
        $videoPath = "$sectionDirectory/$videoTitle.mp4";
        // Get video url from API
        $videoURL = $this->getVideoUrl($video['id'], $courseSlug, $videoTitle);
        // Download if a valid url was retrieved
        if ($videoURL) {
            $sectionVideoTitle = $sectionTitle . " - " . $videoTitle;
            $this->httpClient->downloadFile($videoURL, $videoPath, $sectionVideoTitle);
        }
    }

    private function getVideoUrl(
        int $videoId,
        string $courseSlug,
        string $videoTitle
    ): string | false {
        // Get video url from API
        $availableVideos = $this->httpClient->get("/mobile/courses/$courseSlug/busca-video-$videoId");

        // Sort out videos available by quality
        $videoLinks = [];
        foreach ($availableVideos as $key => $video) {
            $videoLinks[$video['quality']] = $video['link'];
        }

        // Return video url based on user preference or descending order of quality available
        foreach ($this->qualityOrder as $quality) {
            if (array_key_exists($quality, $videoLinks)) {
                return $videoLinks[$quality];
            }
        }
        echo "No video URL found following allowed qualities for video: $videoTitle." . PHP_EOL;
        return false;
    }

    private function createDirectory(string $directoryPath): void
    {
        if (!file_exists($directoryPath)) {
            mkdir($directoryPath, 0777, true);
        }
    }

    // . Getters and Setters
    /**
     * Set the root directory where the courses will be downloaded.
     * @param string $outputDirRoot
     */
    public function setOutputDirRoot(string $outputDirRoot): void
    {
        $this->outputDirRoot = $outputDirRoot;
    }

    /**
     * Set the order of video qualities to be downloaded.
     * The first quality in the array will be the first to be tried.
     * @param string[] $qualityOrder
     */
    public function setQualityOrder(array $qualityOrder): void
    {
        foreach ($qualityOrder as $quality) {
            if (!in_array($quality, ['hd', 'sd'])) {
                echo "Invalid quality: $quality. Valid qualities are: 'hd' and 'sd'." . PHP_EOL;
                return;
            }
        }
        $this->qualityOrder = $qualityOrder;
    }
}

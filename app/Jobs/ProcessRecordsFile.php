<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redis;
use Illuminate\Log\Logger;
use Throwable;

class ProcessRecordsFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Redis storage key of the record pointer.
     *
     * This pointer is used to make sure that
     * all records are begin put into the queue.
     */
    const REDIS_RECORD_POINTER = 'RECORD_POINTER';

    /**
     * Redis storage key of the content hash.
     *
     * This hash is used to verify if there
     * was an previous job processing the same
     * content but didnt finish.
     */
    const REDIS_CONTENT_HASH = 'CONTENT_HASH';

    /**
     * Name of the file to process.
     *
     * @var string
     */
    protected $fileName;

    /**
     * Create a new job instance.
     *
     * @param string $fileName Name of the file to process.
     *
     * @return void
     */
    public function __construct(string $fileName)
    {
        $this->fileName = $fileName;
    }

    /**
     * Returns pointer from Redis storage.
     *
     * @return int
     */
    protected function getPointer()
    {
        return (int)Redis::get(self::REDIS_RECORD_POINTER);
    }

    /**
     * Set pointer in Redis storage.
     *
     * @param int $pointer Record pointer.
     *
     * @return QueueUploadedUsersFile
     */
    protected function setPointer(int $pointer)
    {
        Redis::set(self::REDIS_RECORD_POINTER, $pointer);
        return $this;
    }

    /**
     * Returns content hash from Redis storage.
     *
     * @return string
     */
    protected function getContentHash()
    {
        return Redis::get(self::REDIS_CONTENT_HASH);
    }

    /**
     * Set content hash in Redis storage.
     *
     * @param string $content The file content.
     *
     * @return QueueUploadedUsersFile
     */
    protected function setContentHash(string $content)
    {
        $hash = md5($content);

        Redis::set(self::REDIS_CONTENT_HASH, $hash);
        return $this;
    }

    /**
     * Check if this job was previously
     * executed but did not finish.
     *
     * @param string $content Content of the uploaded file.
     * @param int    $total   Total records.
     *
     * @return boolean
     */
    protected function isContinueFromUnfinishedJob(
        string $content,
        int $total
    ) {
        $hash = $this->getContentHash();
        $pointer = $this->getPointer();

        $contentMatch = md5($content) === $hash;
        $allRecordsProcessed = $total == $pointer;

        return $contentMatch ? !$allRecordsProcessed : false;
    }

    /**
     * Create a new job holding the user data.
     *
     * @param array $record
     *
     * @return StoreNewUser
     */
    protected function createJob(array $record)
    {
        return new StoreNewUser($record);
    }

    /**
     * Queue a new job for a record.
     *
     * @param array $record User data.
     */
    protected function queueJob(array $record)
    {
        $job = $this->createJob($record);
        dispatch($job)->onConnection('redis');
    }

    /**
     * Queues a new job for every record.
     *
     * @param array $records Records from file content.
     *
     * @return void
     */
    protected function queueJobs(array $records, Logger $log)
    {
        foreach ($records as $index => $record) {
            try {
                $this->queueJob($record);
            } catch (Throwable $e) {
                $json = json_encode($record);
                $log->info($json);
            }
            $this->setPointer($index + 1);
        }
    }

    /**
     * Create a new job for every record. Keep track
     * of which records are uploaded in the queue
     * by using redis.
     *
     * @param FailedUserLog $log Log file to store failed attempts.
     *
     * @return void
     */
    public function handle(Logger $log)
    {
        $content = Storage::get($this->fileName);
        $records = json_decode($content, true);
        $total = count($records);

        if ($this->isContinueFromUnfinishedJob($content, $total)) {
            $pointer = $this->getPointer();
            $records = array_slice($records, $pointer);
        }
        $this->setContentHash($content);
        $this->queueJobs($records, $log);
    }
}

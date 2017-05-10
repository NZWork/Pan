<?php

namespace App\Jobs;

use App\Http\Controllers\FileController;
use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class FileMerge extends Job implements ShouldQueue
{
	use InteractsWithQueue, SerializesModels;

	protected $path;
	protected $chunks;
	protected $file;
	protected $uid;

	/**
	 * Create a new job instance.
	 *
	 * @return void
	 */
	public function __construct($path, $chunks, $uid, $file)
	{
		$this->path = $path;
		$this->chunks = $chunks;
		$this->file = $file;
		$this->uid = $uid;
	}

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle()
	{
		if($this->attempts() > 1){
			return;
		}
		echo $this->file['name'] . "\n";
		$ctlFile = new FileController();
		$ctlFile->_fileMerge("storage/" . $this->path, $this->chunks);
		echo "合并完成\n";
		$ctlFile->_putStore($this->path, $this->file, $this->uid, FALSE);
		echo "存储成功\n";
	}
}

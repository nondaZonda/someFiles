<?php
namespace App\Controller;

use App\Error\Error;
use App\Event\ProcessJobsListener;
use App\Exception\ApiException;
use App\Model\Entity\ProcessActivityLog;
use App\Model\Entity\ProcessJob;
use Cake\Event\Event;

class ProcessJobsController extends AppController
{
    public function initialize()
    {
        parent::initialize();
        
        $listener = new ProcessJobsListener();
        $this->eventManager()->on($listener);
    }
    
    public function view($id = null)
    {
        $this->respond('processJob', $this->ProcessJobs->get($id));
    }

    public function viewAll()
    {
        $this->respond('processJobs', $this->paginate($this->ProcessJobs->find('all')));
    }
    
    /**
     * Routing: (GET) : /users/:userId/jobs
     */
    public function viewUsersAll($userId = null)
    {
        $this->respond('processJobs', $this->paginate($this->ProcessJobs->find('usersJobs', ['user_id' => $userId])));
    }
    
    /**
     * Routing: (GET) : /process-items/:itemId/jobs
     */
    public function viewItemsAll($itemId = null)
    {
        $this->respond('processJobs', $this->paginate($this->ProcessJobs->find('jobsForItem', ['item_id' => $itemId])));
    }

    /**
     * Routing: (POST) : /process-items/:item_id/jobs/:job_id/accept
     * W zapytaniu przesłać metodą POST dane w formacie json.
     * Przykład:
     *  {
     *      "pic_id" : [1,21,6,19]
     *  }
     */
    public function acceptPics($itemId, $jobId)
    {
        if (empty($this->request->data)) {
            throw new ApiException(Error::VALIDATION_ERROR, 'Brak przesłanych danych w formacie json!');
        }
        
        $processImages = $this->loadModel('ProcessImages');
        if (!$processImages->setDone($this->request->data, $itemId)) {
            throw new ApiException(Error::INTERNAL_ERROR, 'Zmiana typów zdjęć, nie powiodła się!');
        }
        
        $this->ProcessJobs->setStatus($jobId, ProcessJob::STATUS_DONE);
        
        $event = new Event('Controller.ProcessJobs.statusChange', null, [
            'userId' => $this->Auth->user('id'),
            'itemId' => $itemId,
            'eventType' => ProcessActivityLog::JOB_STATUS_CHANGE_DONE
        ]);
        $this->eventManager()->dispatch($event);
        
        $this->respond();
    }
    
    /**
     * Routing (POST) : /process-items/:item_id/jobs/:job_id/start
     */
    public function start($itemId, $jobId)
    {
        $this->loadModel('ProcessItems');
        if ($this->ProcessItems->hasJobsInProgress($itemId)) {
            throw new ApiException(Error::INTERNAL_ERROR, 'Istnieją już zadania w trakcie pracy !');
        }
        if (!$this->ProcessJobs->setStatus($jobId, ProcessJob::STATUS_IN_PROGRESS)) {
            throw new ApiException(Error::INTERNAL_ERROR, 'Zmiana statusu nie powiodła się!');
        }
        
        $event = new Event('Controller.ProcessJobs.statusChange', null, [
            'userId' => $this->Auth->user('id'),
            'itemId' => $itemId,
            'eventType' => ProcessActivityLog::JOB_STATUS_CHANGE_IN_PROGRESS
        ]);
        $this->eventManager()->dispatch($event);
        
        $this->respond();
    }
    
    /**
     * Routing (POST) : /process-items/:item_id/jobs/:job_id/cancel
     */
    public function cancel($jobId)
    {
        $this->ProcessJobs->setStatus($jobId, ProcessJob::STATUS_CANCELED);
        
        /** @var \App\Model\Entity\ProcessJob $jobEntity */
        $jobEntity = $this->ProcessJobs->get($jobId);
        $event = new Event('Controller.ProcessJobs.statusChange', null, [
            'userId' => $this->Auth->user('id'),
            'itemId' => $jobEntity->item_id,
            'eventType' => ProcessActivityLog::JOB_STATUS_CHANGE_CANCELED
        ]);
        $this->eventManager()->dispatch($event);
        
        $this->respond();
    }
    
    /**
     * Routing: (GET): /process-jobs/feed
     */
    public function feedView()
    {
        $doneJobs = $this->paginate($this->ProcessJobs->find('statusDone'));
        $jobs = $this->ProcessJobs->formatImagesArray($doneJobs);
        
        $this->respond('processJobs', $jobs);
    }

}
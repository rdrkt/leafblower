<?php
class QueueController extends AppController {
    public $uses = array('Queue.Job');
    public $helpers = array();
  
    public function index(){
      $lastQueue = Cache::read('Queue.last', 'queue');
      
      $queue = array();
      $meta = array();
      $tubes = $this->Job->listTubes();
      
      foreach($tubes as $tube){
        $data = $this->Job->statsTube($tube);
        
        if(!empty($data)){
          $queue[$tube] = array_slice($data, 0, 6);
          
          if(!empty($lastQueue[$tube]['current-jobs-ready'])){
            $meta[$tube] = $data['current-jobs-ready'] - $lastQueue[$tube]['current-jobs-ready'];
          }
        }
      }
      
      Cache::write('Queue.last', $queue, 'queue');
      $this->set('queue', $queue);
      $this->set('meta', $meta);
    }
    
    public function kick($tube, $repeat=0){
      $count = $this->Job->kick(array('tube'=>$tube, 'bound'=>500));
      
      if ($repeat){
        $repeat--;
        $url = array('plugin' => 'Queue', 'controller' => 'Queue', 'action' => 'kick', $tube, $repeat);
      } else {
        $url = array('plugin' => 'Queue', 'controller' => 'Queue', 'action'=>'index');
      }
      
      $this->redirect($url);
    }
    
    public function delete($tube, $repeat=0){
      $count = $this->Job->purge(array('tube'=>$tube, 'type'=>'ready', 'bound'=>500));
      $count += $this->Job->purge(array('tube'=>$tube, 'type'=>'reserved', 'bound'=>500));
      $count += $this->Job->purge(array('tube'=>$tube, 'type'=>'buried', 'bound'=>500));
      
      if ($repeat){
        $repeat--;
        $url = array('plugin' => 'Queue', 'controller' => 'Queue', 'action' => 'delete', $tube, $repeat);
      } else {
        $url = array('plugin' => 'Queue', 'controller' => 'Queue', 'action'=>'index');
      }
      
      $this->redirect($url);
    }
    
    public function purge($tube, $repeat=0){
      $count = $this->Job->purge(array('tube'=>$tube, 'bound'=>500));
      
      if ($repeat){
        $repeat--;
        $url = array('plugin' => 'Queue', 'controller' => 'Queue', 'action' => 'purge', $tube, $repeat);
      } else {
        $url = array('plugin' => 'Queue', 'controller' => 'Queue', 'action'=>'index');
      }
      
      $this->redirect($url);
    }
}
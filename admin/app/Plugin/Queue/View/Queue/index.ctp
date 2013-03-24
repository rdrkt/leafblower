<h2>Queue Summary</h2>
  <table class="table table-striped table-condensed table-bordered">
    <thead>
      <tr>
        <th>Tube Name</th>
        <th>Urgent</th>
        <th>Ready</th>
        <th>Reserved</th>
        <th>Delayed</th>
        <th>Buried</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
    <?php
      foreach($queue as $tube=>$data){
        if(!empty($meta[$tube])){
          $icon = ($meta[$tube] > 0) ? " <i class=\"icon-arrow-up\"></i> " : "";
          $icon = ($meta[$tube] < 0) ? " <i class=\"icon-arrow-down\"></i> " : $icon;
          $data['current-jobs-ready'] .= $icon;
        }
        
        $delete = $this->Html->tag('i', '', array('class'=>'icon-ban-circle icon-white'));
        $actions = $this->Html->tag('a', $delete, 
          array(
            'title' => 'Delete Tube',
            'href' => $this->Html->url(
              array('plugin' => 'Queue', 'controller' => 'Queue', 'action' => 'delete', $tube, floor($data['current-jobs-buried'] / 500))),
              'class' => 'btn btn-mini btn-inverse',
              
          )
        );
        
        if(isset($data['current-jobs-buried'])){
          if($data['current-jobs-buried'] > 0){
            $repeat = floor($data['current-jobs-buried'] / 500);
            
            $purge = $this->Html->tag('i', '', array('class'=>'icon-trash'));
            $actions .= $this->Html->tag('a', $purge, array('title' => 'Purge', 'href' => $this->Html->url(array('plugin' => 'Queue', 'controller' => 'Queue', 'action' => 'purge', $tube, $repeat)), 'class' => 'btn btn-mini'));
            
            $kick = $this->Html->tag('i', '', array('class'=>'icon-repeat'));
            $actions .= $this->Html->tag('a', $kick, array('title' => 'Kick', 'href' => $this->Html->url(array('plugin' => 'Queue', 'controller' => 'Queue', 'action' => 'kick', $tube, $repeat)), 'class' => 'btn btn-mini'));
            
            $actions = $this->Html->tag('div', $actions, array('class'=>'btn-group'));
          }
          
          $data['actions'] = $actions;
        }
        
        echo $this->Html->tableCells($data);
      }
    ?>
    </tbody>
  </table>
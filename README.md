yii-masonry
===========

wrapper the great jquery plugin masonry for the yii community




usage example:

<?php

$this->widget(
    'common.widgets.masonry.JMasonry',
    array(
        'container' =>'#container',
        'options'=>"js:
           {
                // options
              itemSelector : '.item',
             columnWidth: function( containerWidth ) {
              return containerWidth / 5;
             }
           }
       ",

    )
);
?>
<style type="text/css">
    .item {
        width: 220px;
        margin: 10px;
        float: left;
    }
    #container{
        width: 800px;
    }
</style>

<div id="container">
   <?php for($i=0; $i<100 ; $i++): ?>
    <div class="item">
        <?php echo str_repeat("hello $i ",10); ?>
    </div>
   <?php endfor ; ?>
</div>
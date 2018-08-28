<?php if (count($events) > 0) : ?>
  <aside class='widget benyon-calendar-widget-container'>

      <?php if ( isset( $instance[ 'title' ] ) && $instance[ 'title' ] !== "" ) : ?>
          <h3> <?php echo $instance[ 'title' ] ?></h3>
      <?php endif; ?>

      <?php foreach ($events as $event) : ?>
          <div class='event <?php echo ($event['cancellation']) ? "event-type-cancellation" : NULL; ?>'>
              <div class='event-title'><?php echo $event['event_summary']; ?></div>
              <div class='event-times'><?php echo $event['event_date_time']; ?></div>
          </div>
      <?php endforeach; ?>

  </aside>
    <?php else : ?>
      <?php error_log('Benyon Calendar Found no events.'); ?>
<?php endif; ?>

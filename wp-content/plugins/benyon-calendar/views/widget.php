<div class='benyon-calendar-widget-container'>

    <?php if ( isset( $instance[ 'title' ] ) && $instance[ 'title' ] !== "" ) : ?>
        <h3> <?php echo $instance[ 'title' ] ?></h3>
    <?php endif; ?>

    <?php foreach ($events as $event) : ?>
        <div class='event <?php echo ($event['cancellation']) ? "event-type-cancellation" : NULL; ?>'>
            <div class='event-title'><?php echo $event['event_summary']; ?></div>
            <div class='event-times'><?php echo $event['event_date_time']; ?></div>
        </div>
    <?php endforeach; ?>

</div>
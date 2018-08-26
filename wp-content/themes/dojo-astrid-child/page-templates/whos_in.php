<?php
/*
Template Name: Who's in?
*/
?>


<?php get_header(); ?>

    <div id="primary" class="content-area dojo-whos-in">
        <main id="main" class="site-main" role="main">
            <h1>Who's in?</h1>

            <?php if (current_user_can('editor') || current_user_can('administrator')) : ?>

                <?php $todays_attendees = dojo_front_end_register(); ?>
                <?php if (count($todays_attendees) > 0) : ?>

                    <table id="dojo-admin-register-search" class="display">
                        <thead>
                        <tr>
                            <th>Nickname</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Login Time</th>
                            <th>Logout Time</th>
                        </tr>
                        </thead>

                        <tbody>
                        <?php foreach ($todays_attendees as $user) : ?>
                            <tr>
                                <td><?php echo $user['NickName']; ?></td>
                                <td><?php echo $user['FirstName']; ?></td>
                                <td><?php echo $user['LastName']; ?></td>
                                <td><?php echo date('H:i', strtotime($user['Login'])); ?></td>
                                <td><?php echo $user['Logout']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>

                    </table>

                <?php else : ?>
                    <p> . . . no one : (</p>
                <?php endif; ?>

            <?php else : ?>
                <p>You shouldn't be here!!!</p>
                <img src="https://media.giphy.com/media/5ftsmLIqktHQA/giphy.gif" alt="jurassic_park_hacker_rejection">
            <?php endif; ?>

        </main>
    </div>
<?php
get_sidebar();
get_footer();

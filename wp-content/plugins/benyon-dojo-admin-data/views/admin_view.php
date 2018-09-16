<script>
  <?php $admin_view_data = dojo_admin_get_data(); ?>
  var attendanceData = <?php echo json_encode($admin_view_data['attendanceData']); ?>;
  var attendanceTopScoresData = <?php echo json_encode($admin_view_data['attendanceTopScoresData']); ?>;
  var register = <?php echo json_encode($admin_view_data['register']); ?>;
</script>

<div class="dojo-admin-view-container">
    <h1>Dojo Dashboard</h1>

  <div>
    <h2>Attendance Graph</h2>
    <canvas id="attendanceChart" width="400" height="150"></canvas>

    <h2>Most Attended Dojos</h2>
    <canvas id="attendanceTopScores" width="100" height="200"></canvas>

    <h2>Registered Members</h2>
    <canvas id="memberCounter" width="200" height="200"></canvas>

    <h2>Users Data</h2>

    <table id="dojo-admin-register-search" class="display">
      <thead>
        <tr>
          <?php foreach ($admin_view_data['users'][0] as $key => $value) : ?>
            <th><?php echo $key; ?></th>
          <?php endforeach;?>
        </tr>
      </thead>

      <tbody>
        <?php foreach ($admin_view_data['users'] as $user) : ?>
          <tr>
            <?php foreach ($user as $data) :?>
              <td><?php echo $data; ?></td>
            <?php endforeach; ?>
          </tr>
        <?php endforeach; ?>
      </tbody>

    </table>
  </div>
</div>

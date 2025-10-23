<!DOCTYPE html>

<html>

<head>
	<?php
	require 'header_voter.php';
	?>
	<!-- Bootstrap -->
	<link href="css/bootstrap.min.css" rel="stylesheet">

	<link href='http://fonts.googleapis.com/css?family=Ubuntu' rel='stylesheet' type='text/css'>
	<link href='http://fonts.googleapis.com/css?family=Raleway' rel='stylesheet' type='text/css'>
	<link href='http://fonts.googleapis.com/css?family=Oswald' rel='stylesheet' type='text/css'>
	<link href='http://fonts.googleapis.com/css?family=Roboto+Condensed' rel='stylesheet' type='text/css'>

</head>

<body>
	<div class="col-sm-12">
		<?php
		require_once "includes/SessionSecurity.php";
		
		// Initialize secure session
		SessionSecurity::initializeSecureSession();
		include "auth.php";
		include "header_voter.php";
		include "includes/XSSProtection.php";
		
		// Set security headers
		XSSProtection::setSecurityHeaders();
		?>
	</div>
	<div class="container" style="padding:100px;">
		<div class="row">
			<div class="col-sm-12" style="border:2px outset gray;">

				<div class="page-header text-center">
					<h2 class="specialHead">
						<font color="red">🏆 Live Voting Results! 🏆</font>
					</h2>
					<p style="font-size: 16px; color: #666;">Real-time voting statistics - Updated live!</p>
				</div>

				<!-- Programming Languages Results -->
				<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 25px; margin: 20px 0; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
					<h3 style="color: white; text-align: center; margin-bottom: 20px; text-shadow: 2px 2px 4px rgba(0,0,0,0.5);">
						🚀 Programming Languages Results
					</h3>
					<?php
					include "connection.php";
					$stmt_lang = mysqli_prepare($con, 'SELECT * FROM languages ORDER BY votecount DESC');
					mysqli_stmt_execute($stmt_lang);
					$member = mysqli_stmt_get_result($stmt_lang);
					if (mysqli_num_rows($member) == 0) {
						echo '<center><font color="white">No results found</font></center>';
					} else {
						echo '<center><table style="background: rgba(255,255,255,0.95); border-radius: 10px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.2);">
								<tr style="background: linear-gradient(45deg, #FF6B6B, #4ECDC4); color: white; font-weight: bold;">
									<td style="padding: 15px; width: 60px;">Rank</td>		
									<td style="padding: 15px; width: 200px;">Language</td>
									<td style="padding: 15px; width: 250px;">Description</td>
									<td style="padding: 15px; width: 100px;">Votes</td>
									<td style="padding: 15px; width: 150px;">Percentage</td>
								</tr>';
						
						$total_votes = 0;
						$results = [];
						while ($mb = mysqli_fetch_object($member)) {
							$total_votes += $mb->votecount;
							$results[] = $mb;
						}
						
						$rank = 1;
						foreach ($results as $mb) {
							$id = $mb->lan_id;
							$name = $mb->fullname;
							$about = $mb->about;
							$vote = $mb->votecount;
							$percentage = $total_votes > 0 ? round(($vote / $total_votes) * 100, 1) : 0;
							
							$row_color = $rank % 2 == 0 ? '#f8f9fa' : '#ffffff';
							echo '<tr style="background: ' . XSSProtection::escapeAttribute($row_color) . '; transition: all 0.3s;" onmouseover="this.style.background=\'#e3f2fd\'" onmouseout="this.style.background=\'' . XSSProtection::escapeJs($row_color) . '\'">';
							echo '<td style="padding: 12px; text-align: center; font-weight: bold;">' . XSSProtection::escapeHtml($rank) . '</td>';
							echo '<td style="padding: 12px; font-weight: bold; color: #333;">' . XSSProtection::escapeHtml($name) . '</td>';
							echo '<td style="padding: 12px; color: #666;">' . XSSProtection::escapeHtml($about) . '</td>';
							echo '<td style="padding: 12px; text-align: center; font-weight: bold; color: #FF6B6B;">' . XSSProtection::escapeHtml($vote) . '</td>';
							echo '<td style="padding: 12px; text-align: center;">
									<div style="background: #e0e0e0; border-radius: 10px; height: 20px; position: relative;">
										<div style="background: linear-gradient(45deg, #FF6B6B, #4ECDC4); height: 100%; width: ' . XSSProtection::escapeAttribute($percentage) . '%; border-radius: 10px; transition: all 0.3s;"></div>
										<span style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 12px; font-weight: bold;">' . XSSProtection::escapeHtml($percentage) . '%</span>
									</div>
								  </td>';
							echo "</tr>";
							$rank++;
						}
						echo '</table></center>';
					}
					mysqli_stmt_close($stmt_lang);
					?>
				</div>

				<!-- Team Members Results -->
				<div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); padding: 25px; margin: 20px 0; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
					<h3 style="color: white; text-align: center; margin-bottom: 20px; text-shadow: 2px 2px 4px rgba(0,0,0,0.5);">
						👥 Best Team Member Results
					</h3>
					<?php
					$stmt_team = mysqli_prepare($con, 'SELECT * FROM team_members ORDER BY votecount DESC');
					mysqli_stmt_execute($stmt_team);
					$team_member = mysqli_stmt_get_result($stmt_team);
					if (mysqli_num_rows($team_member) == 0) {
						echo '<center><font color="white">No results found</font></center>';
					} else {
						echo '<center><table style="background: rgba(255,255,255,0.95); border-radius: 10px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.2);">
								<tr style="background: linear-gradient(45deg, #f093fb, #f5576c); color: white; font-weight: bold;">
									<td style="padding: 15px; width: 60px;">Rank</td>		
									<td style="padding: 15px; width: 200px;">Team Member</td>
									<td style="padding: 15px; width: 300px;">Role & Expertise</td>
									<td style="padding: 15px; width: 100px;">Votes</td>
									<td style="padding: 15px; width: 150px;">Percentage</td>
								</tr>';
						
						$total_team_votes = 0;
						$team_results = [];
						while ($tm = mysqli_fetch_object($team_member)) {
							$total_team_votes += $tm->votecount;
							$team_results[] = $tm;
						}
						
						$rank = 1;
						foreach ($team_results as $tm) {
							$id = $tm->member_id;
							$name = $tm->fullname;
							$about = $tm->about;
							$vote = $tm->votecount;
							$percentage = $total_team_votes > 0 ? round(($vote / $total_team_votes) * 100, 1) : 0;
							
							$row_color = $rank % 2 == 0 ? '#f8f9fa' : '#ffffff';
							$crown = $rank == 1 ? '👑 ' : '';
							echo '<tr style="background: ' . XSSProtection::escapeAttribute($row_color) . '; transition: all 0.3s;" onmouseover="this.style.background=\'#fce4ec\'" onmouseout="this.style.background=\'' . XSSProtection::escapeJs($row_color) . '\'">';
							echo '<td style="padding: 12px; text-align: center; font-weight: bold;">' . XSSProtection::escapeHtml($crown . $rank) . '</td>';
							echo '<td style="padding: 12px; font-weight: bold; color: #333;">' . XSSProtection::escapeHtml($name) . '</td>';
							echo '<td style="padding: 12px; color: #666;">' . XSSProtection::escapeHtml($about) . '</td>';
							echo '<td style="padding: 12px; text-align: center; font-weight: bold; color: #f5576c;">' . XSSProtection::escapeHtml($vote) . '</td>';
							echo '<td style="padding: 12px; text-align: center;">
									<div style="background: #e0e0e0; border-radius: 10px; height: 20px; position: relative;">
										<div style="background: linear-gradient(45deg, #f093fb, #f5576c); height: 100%; width: ' . XSSProtection::escapeAttribute($percentage) . '%; border-radius: 10px; transition: all 0.3s;"></div>
										<span style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 12px; font-weight: bold;">' . XSSProtection::escapeHtml($percentage) . '%</span>
									</div>
								  </td>';
							echo "</tr>";
							$rank++;
						}
						echo '</table></center>';
					}
					mysqli_stmt_close($stmt_team);
					?>
				</div>
				<br>
			</div>
		</div>

	</div>
	<!-- Footer -->
	<nav class="navbar fixed-bottom navbar-light bg-light">
		<footer class="page-footer font-small special-color-dark pt-4">
			<div class="footer-copyright text-center py-3">© 2025 Copyright:
				<a href="/"> Online Voting System by Himanshu Kumar </a>
			</div>
		</footer>
	</nav>

</body>

</html>
<!DOCTYPE html>
<html lang="<?php echo $OJ_LANG ?>">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="keyword" content="<?php echo str_replace(" ", "", $row['source']) ?>">
	<meta name="author" content="<?php echo $OJ_NAME ?>">
	<link rel="shortcut icon" href="/favicon.ico">

	<title>
		<?php echo $row['title'] . " - " . $OJ_NAME ?>
	</title>

	<?php include("template/css.php"); ?>

	<style>
		.modal__container,
		.modal__content {
			height: auto !important;
			max-width: 500px !important;
		}
	</style>

	<link rel="stylesheet" href="<?php echo $OJ_CDN_URL . "katex/" ?>katex.min.css">
	<script defer src="<?php echo $OJ_CDN_URL . "katex/" ?>katex.min.js"></script>
	<script defer src="<?php echo $OJ_CDN_URL . "katex/" ?>contrib/auto-render.min.js"></script>
	<script>
		document.addEventListener("DOMContentLoaded", function() {
			renderMathInElement(document.body, {
				// customised options
				// • auto-render specific keys, e.g.:
				delimiters: [{
						left: '$$',
						right: '$$',
						display: true
					},
					{
						left: '$',
						right: '$',
						display: false
					},
					{
						left: '\\(',
						right: '\\)',
						display: false
					},
					{
						left: '\\[',
						right: '\\]',
						display: true
					}
				],
				// • rendering keys, e.g.:
				throwOnError: false
			});
		});
	</script>
	<style>
		.jumbotron1 {
			font-size: 18px;
		}
	</style>
</head>


<body>
	<div class="modal micromodal-slide" id="modal-1" aria-hidden="true">
		<div class="modal__overlay" tabindex="-1" data-micromodal-close>
			<div class="modal__container" role="dialog" aria-modal="true" aria-labelledby="modal-1-title">
				<main class="modal__content" id="modal-1-content">
					<div id="center" class="table-responsive">
						<div id="llm-response"></div>
						<div class="fs-1 text-center" id="loading"><?php echo $MSG_LOADING ?></div>
					</div>
				</main>
			</div>
		</div>
	</div>

	<div class="container">
		<?php include("template/nav.php"); ?>

		<!-- Main component for a primary marketing message or call to action -->
		<!-- <div class="jumbotron"></div> -->
		<div class='jumbotron'>
			<div class="panel panel-default panel-lg">
				<div class="panel-heading">
					<?php
					if ($pr_flag) {
						echo "<center><h3>$id: " . $row['title'] . "</h3></center>";
					} else {
						//$PID="ABCDEFGHIJKLMNOPQRSTUVWXYZ";
						$id = $row['problem_id'];
						echo "<center><h3>$MSG_PROBLEM " . $PID[$pid] . ": " . $row['title'] . "</h3></center>";
					}
					echo "<div align=right><sub>[$MSG_Creator : <span id='creator'><a href='userinfo.php?user=" . htmlentities($creator, ENT_QUOTES, 'utf-8') . "'>" . htmlentities($creator, ENT_QUOTES, 'utf-8') . "</a></span>]</sub></div>";
					echo "<center>";
					echo "<span class=green>$MSG_Time_Limit : </span><span><span fd='time_limit' pid='" . $row['problem_id'] . "'  >" . $row['time_limit'] . "</span></span> sec&nbsp;&nbsp;";
					echo "<span class=green>$MSG_Memory_Limit : </span>" . $row['memory_limit'] . " MB";
					if ($row['spj']) echo "&nbsp;&nbsp;<span class=red>Special Judge</span>";
					echo "<br><br>";
					echo "<div class='btn-group' role='group'>";
					if ($pr_flag) {
						echo "<a class='btn btn-info btn-sm' href='submitpage.php?id=$id' role='button'>$MSG_SUBMIT</a>";
					} else {
						echo "<a class='btn btn-info btn-sm' href='submitpage.php?cid=$cid&pid=$pid&langmask=$langmask' role='button'>$MSG_SUBMIT</a>";
						if (isset($s_id)) {
							echo "<a class='btn btn-info btn-sm' href='submitpage.php?cid=$cid&pid=$pid&sid=$s_id&langmask=$langmask' role='button'>$MSG_SYNC_HISTORY</a>";
						}
						echo "<a class='btn btn-primary btn-sm' role='button' href='contest.php?cid=$cid'>$MSG_CONTEST-$MSG_LIST</a>";
						if (isset($_SESSION[$OJ_NAME . '_' . 'administrator'])) {
							echo "<a class='btn btn-primary btn-sm' role='button' href=problem.php?id=$id>$MSG_LEAVE_CONTEST</a>";
						}
					}
					if (isset($OJ_OI_MODE) && $OJ_OI_MODE) {
					} else {
						if ($pr_flag) {
							echo "<a class='btn btn-primary btn-sm' role='button' href=status.php?problem_id=" . $row['problem_id'] . "&jresult=4>$MSG_SOVLED: " . $row['accepted'] . "</a>";
							echo "<a class='btn btn-primary btn-sm' role='button' href=status.php?problem_id=" . $row['problem_id'] . ">$MSG_SUBMIT_NUM: " . $row['submit'] . "</a>";
							echo "<a class='btn btn-primary btn-sm' role='button' href=problemstatus.php?id=" . $row['problem_id'] . ">$MSG_STATISTICS</a>";
						} else {
							echo "<a class='btn btn-primary btn-sm' role='button' href=status.php?cid=$cid&problem_id=" . $PID[$pid] . "&jresult=4>$MSG_SOVLED: " . $row['accepted'] . "</a>";
							echo "<a class='btn btn-primary btn-sm' role='button' href=status.php?cid=$cid&problem_id=" . $PID[$pid] . ">$MSG_SUBMIT_NUM: " . $row['submit'] . "</a>";
							echo "<a class='btn btn-primary btn-sm' role='button' href=problemstatus.php?cid=$cid&id=" . $PID[$pid] . ">$MSG_STATISTICS</a>";
						}
					}
					if ($llm_ok) {
						echo "<a class='btn btn-primary btn-sm' role='button' href='javascript:go_render_ana($id)'>$MSG_LLM_ANALYSIS</a>";
					}
					if (isset($_SESSION[$OJ_NAME . '_' . 'administrator']) || isset($_SESSION[$OJ_NAME . '_' . 'contest_creator']) || isset($_SESSION[$OJ_NAME . '_' . 'problem_editor'])) {
						require_once("include/set_get_key.php");
						echo "<a class='btn btn-success btn-sm' role='button' href=admin/problem_edit.php?id=$id&getkey=" . end($_SESSION[$OJ_NAME . '_' . 'getkey']) . ">$MSG_EDIT</a>";
						echo "<a class='btn btn-success btn-sm' role='button' href='admin/phpfm.php?frame=3&pid=" . $row['problem_id'] . "'>$MSG_TESTDATA</a>";
					}
					echo "</div>";
					echo "</center>";
					# end of head
					echo "</div>";
					echo "<!--StartMarkForVirtualJudge-->";
					?>
					<div class="panel-body with-footer">
						<div class="row">
							<div class="col-md-9">
								<div class='panel panel-default'>
									<div class='panel-heading'>
										<h4>
											<?php echo $MSG_Description ?>&nbsp;
											<a class='btn btn-xs btn-info' href="javascript:CopyToClipboard($('.describe').text())"><?php echo $MSG_COPY ?></a>
										</h4>
									</div>
									<div class='panel-body content'>
										<div class='describe text-break'><?php echo $row['description'] ?></div>
									</div>
								</div>
								<div class="row">
									<?php
									if ($row['input']) { ?>
										<div class="col-md-6">
											<div class='panel panel-default'>
												<div class='panel-heading'>
													<h4>
														<?php echo $MSG_Input ?>
													</h4>
												</div>
												<div class='panel-body content text-break'>
													<?php echo $row['input'] ?>
												</div>
											</div>
										</div>
									<?php
									}
									if ($row['output']) { ?>
										<div class="col-md-6">
											<div class='panel panel-default'>
												<div class='panel-heading'>
													<h4>
														<?php echo $MSG_Output ?>
													</h4>
												</div>
												<div class='panel-body content text-break'>
													<?php echo $row['output'] ?>
												</div>
											</div>
										</div>
									<?php } ?>
								</div>
								<div class="row">
									<?php
									$sinput = str_replace("<", "<", $row['sample_input']);
									$sinput = str_replace(">", ">", $sinput);
									$soutput = str_replace("<", "<", $row['sample_output']);
									$soutput = str_replace(">", ">", $soutput);
									if (strlen($sinput)) { ?>
										<div class="col-md-6">
											<div class='panel panel-default'>
												<div class='panel-heading'>
													<h4>
														<?php echo $MSG_Sample_Input ?>&nbsp;
														<a class='btn btn-xs btn-info' href="javascript:CopyToClipboard($('#sampleinput').text())"><?php echo $MSG_COPY ?></a>
													</h4>
												</div>
												<div class='panel-body'>
													<pre class=content><span id="sampleinput" class=sampledata><?php echo $sinput ?></span></pre>
												</div>
											</div>
										</div>
									<?php
									}
									if (strlen($soutput)) { ?>
										<div class="col-md-6">
											<div class='panel panel-default'>
												<div class='panel-heading'>
													<h4>
														<?php echo $MSG_Sample_Output ?>&nbsp;
														<a class='btn btn-xs btn-info' href="javascript:CopyToClipboard($('#sampleoutput').text())"><?php echo $MSG_COPY ?></a>
													</h4>
												</div>
												<div class='panel-body'>
													<pre class=content><span id='sampleoutput' class=sampledata><?php echo $soutput ?></span></pre>
												</div>
											</div>
										</div>
									<?php
									} ?>
								</div>
								<?php
								if ($row['hint']) { ?>
									<div class='panel panel-default'>
										<div class='panel-heading'>
											<h4>
												<?php echo $MSG_HINT ?>
											</h4>
										</div>
										<div class='panel-body content text-break'>
											<?php echo $row['hint'] ?>
										</div>
									</div>
								<?php } ?>
								<?php if ($row['blank']) { ?>
									<div class='panel panel-default'>
										<div class='panel-heading'>
											<h4>
												<?php echo $MSG_BLANK_FILLING ?>
												<a class='btn btn-xs btn-info' href="javascript:CopyToClipboard($('.blank-code').text())"><?php echo $MSG_COPY; ?></a>
											</h4>
										</div>
										<div class='panel-body content' style='padding:10px;'>
											<pre id='code' class="blank-code" style='padding:15px!important;'><?php echo $blank; ?></pre>
										</div>
									</div>
								<?php } ?>
							</div>
							<div class="col-md-3">

								<div class='panel panel-default'>
									<div class='panel-heading'>
										<h4>
											<?php echo $MSG_AB_KEYWORD ?>
										</h4>
									</div>
									<div class='panel-body content'>
										<?php if ($row['allow'] || $row['block']) { ?>
											<?php if ($row['block']) { ?>
												<div style='margin-top:10px;'>
													<?php echo $MSG_BLOCK_KEYWORD ?>:
													<?php foreach (explode(" ", $row["block"]) as $block) { ?>
														<span class='label label-danger label-inline'><?php echo $block; ?></span>
													<?php } ?>
												</div>
											<?php }
											if ($row['allow']) { ?>
												<div style='margin-top:10px;'>
													<?php echo $MSG_ALLOW_KEYWORD ?>:
													<?php foreach (explode(" ", $row["allow"]) as $allow) { ?>
														<span class='label label-success label-inline'><?php echo $allow; ?></span>
													<?php } ?>
												</div>
											<?php } ?>
										<?php } else {
											echo $MSG_EMPTY;
										} ?>
									</div>
								</div>
								<div class='panel panel-default'>
									<div class='panel-heading'>
										<h4>
											<?php echo $MSG_SOURCE ?>
										</h4>
									</div>

									<div fd="source" pid=<?php echo $row['problem_id'] ?> class='panel-body content text-break'>
										<?php if ($row['source']) { ?>
											<?php
											$cats = explode(" ", $row['source']);
											foreach ($cats as $cat) {
												if ($cat == "") continue;
												$hash_num = hexdec(substr(md5($cat), 0, 7));
												$label_theme = $color_theme[$hash_num % count($color_theme)];
												if ($label_theme == "") $label_theme = "default";
												echo "<a class='label label-$label_theme label-inline' href='problemset.php?search=" . urlencode(htmlentities($cat, ENT_QUOTES, 'utf-8')) . "'>" . htmlentities($cat, ENT_QUOTES, 'utf-8') . "</a>&nbsp;";
											} ?>
										<?php } else {
											echo $MSG_EMPTY;
										} ?>
									</div>
								</div>
							</div>
						</div>
					</div>
					<!--EndMarkForVirtualJudge-->
					<div class='panel-footer'>
						<center>
							<?php
							if ($pr_flag) {
								echo "<a class='btn btn-info btn-sm' href='submitpage.php?id=$id' role='button'>$MSG_SUBMIT</a>";
							} else {
								echo "<a class='btn btn-info btn-sm' href='submitpage.php?cid=$cid&pid=$pid&langmask=$langmask' role='button'>$MSG_SUBMIT</a>";
							}
							?>
						</center>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php include("template/js.php"); ?>
	<script src="<?php echo $OJ_CDN_URL . "include/" ?>simpleLightbox.min.js"></script>
	<script>
		$(".content").find("img").each(function(index, elem) {
			var atag = $("<a class='image'></a>")
			atag.attr("href", $(elem).attr("src"))
			$(elem).wrap(atag)
		})
		$(".image").simpleLightbox();
		$("div.content table").each(function(_, elem) {
			table = $(elem);
			if (!table.parent().hasClass("table-responsive")) {
				$(elem).wrap("<div class='table-responsive'></div>");
			}
			if (!table.hasClass("whitespace-nowrap")) {
				$(elem).addClass("whitespace-nowrap")
			}
		})
	</script>
	<script language="javascript" type="text/javascript" src="<?php echo $OJ_CDN_URL ?>template/micromodal.min.js"></script>
	<script>
		MicroModal.init();
		var rendered = false;

		function go_render_ana(pid) {
			if (!rendered) {
				const evtSource = new EventSource("llm_response.php?pid=" + pid, {
					withCredentials: true,
				});
				evtSource.onmessage = function(event) {
					let llmDiv = document.getElementById("llm-response");
					if (event.data === "[DONE]") {
						llmDiv.innerHTML += "<br><br>Powered by " + "<?php echo $OJ_LLM_MODEL ?>" + ".";
						evtSource.close();
						return;
					}
					let parsedData = JSON.parse(event.data);
					let text = parsedData.choices[0].delta.content;
					if (!text) return;
					$("#loading").hide();
					text = text.replace(/\n/g, "<br>");
					llmDiv.innerHTML += text;
				};
				rendered = true;
			}

			MicroModal.show("modal-1");
		}
	</script>
	<?php if ($row["background"]) { ?>
		<style>
			@media (prefers-color-scheme: light) {
				body {
					background: url("<?php echo $row["background"] ?>") no-repeat 50% 50% / cover;
					background-attachment: fixed;
				}

				.container {
					opacity: 0.9;
				}

				.footer-container {
					background: rgba(255, 255, 255, 0.9);
					margin: 18px 2rem 18px 2rem;
					padding: 1em;
					border-radius: 20px;
				}

				.panel {
					background: rgba(255, 255, 255, 0);
				}

				.panel-body {
					background: rgba(255, 255, 255, 1);
				}
			}
		</style>
	<?php } ?>
</body>

</html>
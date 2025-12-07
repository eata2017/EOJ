<!DOCTYPE html>
<html lang="<?php echo $OJ_LANG ?>">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="">
  <meta name="author" content="<?php echo $OJ_NAME ?>">
  <link rel="shortcut icon" href="/favicon.ico">
  <style>
    .modal__container,
    .modal__content {
      height: auto !important;
      max-width: 500px !important;
    }
  </style>

  <title><?php echo  $id . " - " . $OJ_NAME ?></title>
  <?php include("template/css.php"); ?>

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
    <div class="jumbotron">
      <div class="lr-container">
        <div class="table-responsive">
          <table class="table mb-0 w-50">
            <thead>
              <tr>
                <th><?php echo $MSG_RUNID ?></th>
                <th><?php echo $MSG_PROBLEM ?></th>
                <th><?php echo $MSG_USER ?></th>
                <th><?php echo $MSG_NICK ?></th>
                <th><?php echo $MSG_LANG ?></th>
                <th><?php echo $MSG_RESULT ?></span></th>
                <?php if ($show_info["result"] == 4) { ?>
                  <th><?php echo $MSG_TIME ?></th>
                  <th><?php echo $MSG_MEMORY ?></th>
                <?php } ?>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><?php echo $id ?></td>
                <td>
                  <a href="problem.php?id=<?php echo $show_info["problem_id"] ?>">
                    <?php echo $show_info["problem_id"] ?>
                  </a>
                </td>
                <td>
                  <a href="userinfo.php?user=<?php echo $show_info["user_id"] ?>">
                    <?php echo $show_info["user_id"] ?>
                  </a>
                </td>
                <td><?php echo $show_info["nick"] ?></td>
                <td>
                  <a href="showsource.php?id=<?php echo $id ?>">
                    <?php echo $language_name[$show_info["language"]] ?>
                  </a>
                </td>
                <td>
                  <span class="label label-<?php echo $judge_color[$show_info["result"]] ?>">
                    <?php echo $judge_result[$show_info["result"]] ?>
                  </span>
                </td>
                <?php if ($show_info["result"] == 4) { ?>
                  <td><?php echo $show_info["time"] ?> ms</td>
                  <td><?php echo $show_info["memory"] ?> KB</td>
                <?php } ?>
              </tr>
            </tbody>
          </table>
        </div>
        <?php if ($OJ_LLM_API_KEY != "") { ?>
          <button class="btn btn-primary mt-4 mx-2" onclick="go_render_ana(<?php echo $id ?>)">
            <?php echo $MSG_LLM_ANALYSIS ?>
          </button>
        <?php } ?>
      </div>

      <pre id='code' class="alert alert-error"><?php echo $view_reinfo ?></pre>
    </div>
  </div>

  <?php include("template/js.php"); ?>
  <script language="javascript" type="text/javascript" src="<?php echo $OJ_CDN_URL ?>template/micromodal.min.js"></script>

  <script>
    MicroModal.init();
    var rendered = false;

    function go_render_ana(rid) {
      if (!rendered) {
        const evtSource = new EventSource("llm_response.php?sid=" + rid, {
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
</body>

</html>
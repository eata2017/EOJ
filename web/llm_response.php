<?php
$cache_time = 90;
$OJ_CACHE_SHARE = false;
require_once('./include/cache_start.php');
require_once('./include/db_info.inc.php');
require_once('./include/setlang.php');
require_once("./include/const.inc.php");

if (!isset($_GET['sid']) || $OJ_LLM_API_KEY == "") {
    $view_errors = "$MSG_NOT_EXISTED";
    require("template/error.php");
    exit(0);
}

$ok = false;
$sid = intval($_GET['sid']);

$sql = "SELECT * FROM `solution` WHERE `solution_id`=?";
$result = pdo_query($sql, $sid);
$row = $result[0];
$slanguage = $row['language'];
$sresult = $row['result'];
$sproblem_id = $row['problem_id'];
$view_user_id = $suser_id = $row['user_id'];
$slang = $language_ext[$slanguage];
$sresult = $jresult[$row['result']];

if (
    isset($_SESSION[$OJ_NAME . '_' . 'source_browser'])
    || ($_SESSION[$OJ_NAME . '_' . "allow_view"]
        && $suser_id == $_SESSION[$OJ_NAME . '_' . 'user_id'])
) {
    $ok = true;
}

$sql = "SELECT * FROM `source_code` WHERE `solution_id` = ?";
$result = pdo_query($sql, $sid);
$source = $result[0]["source"];

$sql = "SELECT * FROM `runtimeinfo` WHERE `solution_id` = ?";
$result = pdo_query($sql, $sid);
$reinfo = count($result) > 0 ? $result[0]["error"] : "";

$sql = "SELECT * FROM `compileinfo` WHERE `solution_id` = ?";
$result = pdo_query($sql, $sid);
$ceinfo = count($result) > 0 ? $result[0]["error"] : "";

$sql = "SELECT * FROM `problem` WHERE `problem_id` = ?";
$result = pdo_query($sql, $sproblem_id);
$ptitle = $result[0]["title"];
$pdescription = $result[0]["description"];
$pinput = $result[0]["input"];
$poutput = $result[0]["output"];
$psinput = $result[0]["sample_input"];
$psoutput = $result[0]["sample_output"];
$phint = $result[0]["hint"];
$pallow = $result[0]["allow"];
$pblock = $result[0]["block"];
$pblank = $result[0]["blank"];

$lang_map = array("zh" => "中文", "en" => "English");
$output_lang = $lang_map[$OJ_LANG] ?? "English";

$system_prompt = "You are an expert AI code debugger and tutor for a competitive programming platform.
Your role is to analyze user-submitted code against a given problem description,
identify the core logical and/or runtime errors, and provide a constructive, educational critique.
Your response MUST adhere strictly to the following rules:
1. Do NOT provide the correct, working solution code or the exact missing lines of code.
2. Do NOT use the phrase 'The correct solution is...' or similar phrasing that reveals the answer.
3. Do NOT correct the user's code directly.
Instructions:
1. Analyze the Code and Problem: Carefully compare the provided `USER CODE` 
with the `PROBLEM DESCRIPTION` and the `FAILED TEST CASE (if available)`.
2. Identify the Core Error: Determine the most significant issue. This is usually a flaw in the algorithm's logic, 
an incorrect handling of edge cases, or an efficiency (Time Limit Exceeded) problem.
3. Explain the Error (Critique): Write a concise explanation of why the current approach fails.
For example, explain which specific constraint (like a large input size or a specific boundary condition)
the current logic cannot handle, or where the algorithm deviates from the correct mathematical/algorithmic principle.
4. Provide a Hint/Suggestion: Offer a high-level,
algorithmic hint that guides the user toward the correct solution without giving it away.
Frame it as a strategic question or a key concept to research.
Focus your output ONLY on the following two sections:
A brief, educational explanation of the error, and a guiding suggestion.
Respond in pure text format without any additional formatting or code blocks.
Please respond in $output_lang.";

$user_prompt = "Please analyze the following details carefully.
PROBLEM TITLE:
$ptitle
PROBLEM DESCRIPTION:
$pdescription
INPUT:
$pinput
OUTPUT:
$poutput
SAMPLE INPUT:
$psinput
SAMPLE OUTPUT:
$psoutput
HINT:
$phint
KEYWORD NOT ALLOWED IN ANSWER SOURCE:
$pblock
KEYWORD MUST IN ANSWER SOURCE:
$pallow
THE TEMPLATE CODE (IF ANY) THAT MUST NOT BE MODIFIED (*%* FOR MULTILINE, %*% FOR SINGLE LINE):
$pblank
USER'S SUBMITTED CODE LANGUAGE:
$slang
JUDGE RESULT:
$sresult
USER CODE:
$source
RUNTIME ERROR MESSAGE (IF ANY):
$reinfo
COMPILE ERROR MESSAGE (IF ANY):
$ceinfo";

$data = array(
    "model" => $OJ_LLM_MODEL,
    "messages" => array(
        array("role" => "system", "content" => $system_prompt),
        array("role" => "user", "content" => $user_prompt)
    ),
    "stream" => true
);
$payload = json_encode($data);
$options = [
    'http' => [
        'method'  => 'POST',
        'header'  => "Content-type: application/json\r\n" .
            "Authorization: Bearer " . $OJ_LLM_API_KEY . "\r\n",
        'content' => $payload
    ]
];

$context = stream_context_create($options);
$url = $OJ_LLM_URL . "/chat/completions";

$remote_stream = @fopen($url, 'r', false, $context);

if ($remote_stream === FALSE) {
    http_response_code(500);
    echo "Error connecting to remote server.";
    exit;
}

@ini_set('zlib.output_compression', 0);
@ini_set('implicit_flush', 1);
@ob_implicit_flush(1);
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('X-Accel-Buffering: no');

$chunk_size = 256;
while (!feof($remote_stream)) {
    $chunk = fread($remote_stream, $chunk_size);
    echo $chunk;
    ob_flush();
    flush();
}
fclose($remote_stream);

if (file_exists('./include/cache_end.php'))
    require_once('./include/cache_end.php');

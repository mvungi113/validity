<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logical Argument Validator</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script>
        function insertSymbol(symbol) {
            var input = document.getElementById("expression");
            var startPos = input.selectionStart;
            var endPos = input.selectionEnd;
            input.value = input.value.substring(0, startPos) + symbol + input.value.substring(endPos, input.value.length);
            input.focus();
            input.selectionStart = startPos + symbol.length;
            input.selectionEnd = startPos + symbol.length;
        }

        function resetForm() {
            document.getElementById("expression").value = "";
            document.getElementById("result").innerHTML = "";
        }
    </script>
</head>

<body>
    <div class="container mt-5">
        <h1 class="text-center">Logical Argument Validator</h1>
        <form method="post" class="mt-4">
            <div class="form-group">
                <label for="expression">Enter Logical Expression:</label>
                <input type="text" id="expression" name="expression" class="form-control" required>
            </div>
            <div class="btn-group mb-3" role="group" aria-label="Logical Operators">
                <button type="button" class="btn btn-secondary" onclick="insertSymbol('∧')">∧</button>
                <button type="button" class="btn btn-secondary" onclick="insertSymbol('∨')">∨</button>
                <button type="button" class="btn btn-secondary" onclick="insertSymbol('¬')">¬</button>
                <button type="button" class="btn btn-secondary" onclick="insertSymbol('→')">→</button>
                <button type="button" class="btn btn-secondary" onclick="insertSymbol('↔')">↔</button>
            </div>
            <div>
                <input type="submit" class="btn btn-primary" value="Generate Truth Table">
                <button type="button" class="btn btn-warning" onclick="resetForm()">Reset</button>
            </div>
        </form>

        <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $expression = $_POST["expression"];
            if (validateSyntax($expression)) {
                generateTruthTable($expression);
            } else {
                echo "<div class='alert alert-danger mt-4'>Invalid syntax in the logical expression.</div>";
            }
        }

        function validateSyntax($expression)
        {
            $stack = [];
            $validChars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ∧∨¬→↔() ';
            for ($i = 0; $i < strlen($expression); $i++) {
                $char = $expression[$i];
                if (strpos($validChars, $char) === false) {
                    return false;
                }
                if ($char == '(') {
                    array_push($stack, $char);
                } elseif ($char == ')') {
                    if (empty($stack)) {
                        return false;
                    }
                    array_pop($stack);
                }
            }
            return empty($stack);
        }

        function generateTruthTable($expression)
        {
            $variables = [];
            preg_match_all('/[a-zA-Z]/', $expression, $variables);
            $variables = array_unique($variables[0]);
            $numRows = pow(2, count($variables));
            $results = [];

            echo "<table class='table table-bordered mt-4'>";
            echo "<thead class='thead-dark'>";
            echo "<tr>";
            foreach ($variables as $var) {
                echo "<th>{$var}</th>";
            }
            echo "<th>{$expression}</th>";
            echo "</tr>";
            echo "</thead>";
            echo "<tbody>";

            for ($i = 0; $i < $numRows; $i++) {
                $values = [];
                foreach ($variables as $j => $var) {
                    $values[$var] = ($i >> (count($variables) - $j - 1)) & 1 ? 'T' : 'F';
                }
                echo "<tr>";
                foreach ($variables as $var) {
                    echo "<td>{$values[$var]}</td>";
                }
                $evaluated = evaluateExpression($expression, $values);
                echo "<td>{$evaluated}</td>";
                echo "</tr>";
                $results[] = $evaluated;
            }
            echo "</tbody>";
            echo "</table>";

            checkTautologyAndValidity($results);
        }

        function evaluateExpression($expression, $values)
        {
            foreach ($values as $var => $val) {
                $expression = str_replace($var, $val, $expression);
            }
            $expression = str_replace(['T', 'F', '∧', '∨', '¬', '→', '↔'], ['true', 'false', ' && ', ' || ', ' ! ', ' => ', ' == '], $expression);


            $expression = preg_replace('/([a-zA-Z]+)\s*=>\s*([a-zA-Z]+)/', '(!$1 || $2)', $expression);
            $expression = preg_replace('/([a-zA-Z]+)\s*==\s*([a-zA-Z]+)/', '(($1 && $2) || (!$1 && !$2))', $expression);

            $expression = 'return ' . $expression . ';';
            $evaluated = eval($expression) ? 'T' : 'F';
            return $evaluated;
        }

        function checkTautologyAndValidity($results)
        {
            $isTautology = true;
            foreach ($results as $result) {
                if ($result !== 'T') {
                    $isTautology = false;
                    break;
                }
            }

            echo "<div id='result' class='mt-4'>";
            echo "<h2>Results</h2>";
            if ($isTautology) {
                echo "<p class='text-success'>The logical expression is a tautology.</p>";
                echo "<p class='text-success'>The argument is valid.</p>";
            } else {
                echo "<p class='text-danger'>The logical expression is not a tautology (contradiction).</p>";
                echo "<p class='text-danger'>The argument is not valid (fallacy).</p>";
            }
            echo "</div>";
        }
        ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>
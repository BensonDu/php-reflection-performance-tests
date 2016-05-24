<?php

/**
 * PHP Reflection Performance Tests
 */
class TestEngine {
    const NUM_CLASSES = 1000;
    const NUM_PROPERTIES = 50;
    const NUM_METHODS = 50;
    const TMP_DIRECTORY = __DIR__ . "/tmp";

    static $numClasses = 1000;
    static $numProperties = 50;
    static $numMethods = 50;

    static $footprintLastTime = null;
    static $footprintLastCompareTime = null;
    static $footprintLastMemory = null;
    static $footprintLastCompareMemory = null;

    static $allClasses = array();

    static $tests = array();

    static function addTest($title, Closure $test) {
        self::$tests[] = array("title" => $title, "test" => $test);
    }

    static function setFootprintValues() {
        self::$footprintLastTime = microtime(true);
        self::$footprintLastMemory = memory_get_usage(true);
    }

    static function showFootprint($compareWithPast = false) {
        $compareTime = round((microtime(true) - self::$footprintLastTime) * 1000, 4);
        $compareMemory = (memory_get_usage(true) - self::$footprintLastMemory) / 1024;
        echo '<div>';
        echo 'Needed Time: ' . $compareTime . ' ';
        if ($compareWithPast) self::showCompareNumber($compareTime, self::$footprintLastCompareTime);
        echo ' milliseconds (1000ms = 1s)<br/>';
        echo 'Memory consumption ' . $compareMemory . ' ';
        if ($compareWithPast) self::showCompareNumber($compareMemory, self::$footprintLastCompareMemory);
        echo ' kilobytes';
        echo '</div>';
        self::$footprintLastCompareTime = $compareTime;
        self::$footprintLastCompareMemory = $compareMemory;
    }

    static function showCompareNumber($a, $b) {
        if ($a > $b) {
            $percent = round((100 / $b) * $a, 2);
            echo '<span style="color:red">(' . ($a - $b) . 'ms more / ' . $percent . '% slower)</span>';
        } else if ($a < $b) {
            $percent = round((100 / $a) * $b, 2);
            echo '<span style="color:green">(' . ($b - $a) . 'ms less / ' . $percent . '% faster)</span>';
        } else {
            echo "<span style='color:#777777;'>(equal)</span>";
        }
    }

    static function autoload($className) {
        if (!class_exists($className, false)) {
            require self::TMP_DIRECTORY . "/" . $className . ".class.php";
        }
    }
}

spl_autoload_register("TestEngine::autoload");

if (isset($_GET["run"]) && $_GET["run"]) {

    TestEngine::$numClasses = (int)$_GET["num_classes"];
    TestEngine::$numProperties = (int)$_GET["num_properties"];
    TestEngine::$numMethods = (int)$_GET["num_methods"];

    # empty temporary directory
    $files = scandir(TestEngine::TMP_DIRECTORY);
    foreach ($files as $file) {
        if (substr($file, 0, 1) == ".") continue;
        unlink(TestEngine::TMP_DIRECTORY . "/" . $file);
    }

# create given number of class files
    for ($i = 1; $i <= TestEngine::$numClasses; $i++) {
        $className = "Foo" . md5(uniqid(null, true)) . "_$i";
        $filename = $className . ".class.php";
        $str = "<?php\n";
        $str .= "/**\n * Just a demo doc comment\n */\n";
        $str .= "class " . $className;
        if (rand(0, 1)) $str .= " extends Exception";
        if (rand(0, 1)) $str .= " implements Countable";
        $str .= " {\n";
        # create random properties
        for ($p = 1; $p <= TestEngine::$numProperties; $p++) {
            $propertyName = "prop" . md5(uniqid(null, true)) . "_{$i}_{$p}";
            $str .= "    /**\n     * Just another demo comment for $propertyName\n     * @type int\n     */\n    ";
            switch (rand(0, 3)) {
                case 0:
                    $str .= "private";
                    break;
                case 1:
                    $str .= "public";
                    break;
                case 2:
                    $str .= "static";
                    break;
                case 3:
                    $str .= "protected";
                    break;
            }
            $str .= " \$" . $propertyName . ";\n\n";
        }
        # include count function for implements
        $str .= "    /**\n     * Just another demo comment fo\n     * @return int\n     */\n    ";
        $str .= "public function count() {\n        return 1;\n    }\n\n";

        # create random methods
        for ($m = 1; $m <= TestEngine::$numMethods; $m++) {
            $methodName = "method" . md5(uniqid(null, true)) . "_{$i}_{$m}";
            $str .= "    /**\n     * Just another demo comment for $methodName\n     * @return null\n     */\n    ";
            switch (rand(0, 3)) {
                case 0:
                    $str .= "private";
                    break;
                case 1:
                    $str .= "public";
                    break;
                case 2:
                    $str .= "static";
                    break;
                case 3:
                    $str .= "protected";
                    break;
            }
            $str .= " function " . $methodName . "(){\n        return null;\n    }\n\n";
        }
        $str .= "}";
        # save file
        $path = TestEngine::TMP_DIRECTORY . "/" . $filename;
        file_put_contents($path, $str);
        TestEngine::$allClasses[] = $className;

        # preload classes if required
        if (isset($_GET["preload_classes"]) && $_GET["preload_classes"]) require $path;
    }
}

# defining tests
TestEngine::addTest('Get ' . (TestEngine::$numClasses * TestEngine::$numProperties) . ' properties of ' . TestEngine::$numClasses . ' classes', function () {
    TestEngine::setFootprintValues();
    foreach (TestEngine::$allClasses as $class) {
        $reflection = new ReflectionClass($class);
        $properties = $reflection->getProperties();
    }
    TestEngine::showFootprint();
});

TestEngine::addTest('Get ' . (TestEngine::$numClasses * TestEngine::$numMethods) . ' methods of ' . TestEngine::$numClasses . ' classes', function () {
    TestEngine::setFootprintValues();
    foreach (TestEngine::$allClasses as $class) {
        $reflection = new ReflectionClass($class);
        $methods = $reflection->getMethods();
    }
    TestEngine::showFootprint();
});

TestEngine::addTest('Get methods and read doc comments of ' . (TestEngine::$numClasses * TestEngine::$numMethods) . ' methods of ' . TestEngine::$numClasses . ' classes', function () {
    TestEngine::setFootprintValues();
    foreach (TestEngine::$allClasses as $class) {
        $reflection = new ReflectionClass($class);
        $methods = $reflection->getMethods();
        foreach ($methods as $method) {
            $comment = $method->getDocComment();
        }

    }
    TestEngine::showFootprint();
});

TestEngine::addTest('Get protected methods and read doc comments of ' . (TestEngine::$numClasses * TestEngine::$numMethods) . ' methods of ' . TestEngine::$numClasses . ' classes', function () {
    TestEngine::setFootprintValues();
    foreach (TestEngine::$allClasses as $class) {
        $reflection = new ReflectionClass($class);
        $methods = $reflection->getMethods(ReflectionProperty::IS_PROTECTED);
        foreach ($methods as $method) {
            $comment = $method->getDocComment();
        }

    }
    TestEngine::showFootprint();
});

TestEngine::addTest('property_exists vs. Reflection::hasProperty for ' . (TestEngine::$numClasses * TestEngine::$numProperties) . ' properties of ' . TestEngine::$numClasses . ' classes', function () {
    $propertyName = "prop" . md5(uniqid(null, true));
    echo '<h3>::hasProperty</h3>';
    TestEngine::setFootprintValues();
    foreach (TestEngine::$allClasses as $class) {
        $reflection = new ReflectionClass($class);
        $hasProperty = $reflection->hasProperty($propertyName);
    }
    TestEngine::showFootprint();

    echo '<h3>property_exists()</h3>';
    TestEngine::setFootprintValues();
    foreach (TestEngine::$allClasses as $class) {
        property_exists($class, $propertyName);
    }
    TestEngine::showFootprint(true);
});

TestEngine::addTest('method_exists vs. Reflection::hasMethod for ' . (TestEngine::$numClasses * TestEngine::$numMethods) . ' methods of ' . TestEngine::$numClasses . ' classes', function () {
    $methodName = "method" . md5(uniqid(null, true));
    echo '<h3>::hasMethod</h3>';
    TestEngine::setFootprintValues();
    foreach (TestEngine::$allClasses as $class) {
        $reflection = new ReflectionClass($class);
        $hasMethod = $reflection->hasMethod($methodName);
    }
    TestEngine::showFootprint();

    echo '<h3>method_exists()</h3>';
    TestEngine::setFootprintValues();
    foreach (TestEngine::$allClasses as $class) {
        method_exists($class, $methodName);
    }
    TestEngine::showFootprint(true);
});

TestEngine::addTest('is_subclass_of vs. Reflection::isSubclassOf for ' . (TestEngine::$numClasses * TestEngine::$numMethods) . ' methods of ' . TestEngine::$numClasses . ' classes', function () {
    echo '<h3>::isSubclassOf</h3>';
    TestEngine::setFootprintValues();
    foreach (TestEngine::$allClasses as $class) {
        $reflection = new ReflectionClass($class);
        $check = $reflection->isSubclassOf($class);
    }
    TestEngine::showFootprint();

    echo '<h3>is_subclass_of()</h3>';
    TestEngine::setFootprintValues();
    foreach (TestEngine::$allClasses as $class) {
        is_subclass_of($class, $class);
    }
    TestEngine::showFootprint(true);
});

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PHP5/PHP7 Reflection Performance Tests</title>
</head>
<body>
<?= '<h5>You are running PHP ' . PHP_VERSION . ' on ' . PHP_OS . '</h5>' ?>
<p></p>
<form name="m" method="get" action="">
    <input type="number" name="num_classes" value="<?= TestEngine::$numClasses ?>" step="1"> Number of classes<br/>
    <input type="number" name="num_properties" value="<?= TestEngine::$numProperties ?>" step="1"> Number of properties
    for each class<br/>
    <input type="number" name="num_methods" value="<?= TestEngine::$numMethods ?>" step="1"> Number of methods for each
    class<br/>
    <input type="checkbox" name="preload_classes" value="1" <?= ((isset($_GET["run"]) && $_GET["run"] && isset($_GET["preload_classes"])) || !isset($_GET["run"]) ? 'checked' : '') ?>> Preload classes - If enabled than all classes will
    be preloaded before the first test. If disabled than autoloading is activated -> This will
    falsify the first test because the classes will be loaded when the class is first needed, so, inside the first test.<br/>
    <?php
    foreach (TestEngine::$tests as $key => $row) {
        ?>
        <input type="checkbox"
               name="test[<?= $key ?>]" <?= ((isset($_GET["run"]) && $_GET["run"] && isset($_GET["test"][$key])) || !isset($_GET["run"]) ? 'checked' : '') ?>
               value="<?= $key ?>"> <?= $row["title"] ?><br/>
        <?
    }
    ?>
    <br/>
    <input type="submit" name="run" value="Run">
    <? if (isset($_GET["run"]) && $_GET["run"] && isset($_GET["test"])) {
        foreach ($_GET["test"] as $testKey) {
            $row = TestEngine::$tests[$testKey];
            echo '<h2 style="color:orangered; border-bottom: 1px solid #d5d5d5">' . $row["title"] . '</h2>';
            $row["test"]();
        }
    } ?>
</form>
</body>
</html>

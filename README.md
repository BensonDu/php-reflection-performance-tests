# PHP5 / PHP7 Reflection Performance Tests
Simple and straight forward PHP 5 and PHP 7 reflection performance tests. Feel free to add more tests (pull request) for all reflection tests you know.

# Install and usage
Clone it, place it on your webserver, and open the test.php. Make sure that the `tmp` folder is writable from your php application.

# Overall results and my personal conclusion
* PHP 7 is almost twice as fast as PHP 5 in case of reflections.
* Basic Reflections are quite fast - Reading methods and doc comments for 1000 classes cost a few milliseconds. However, loading the classes into memory is the biggest bottleneck. So it depend on filesize and amount of classes how fast it will be. On our testsystem it takes about 300ms to load 1000 class files into memory (require/include/autoload). 
* Using reflections when you've already loaded the classes into memory than you shouldn't need to worry about the performance impact of reflections.

# Contribute
Please feel free to improve and add more tests.
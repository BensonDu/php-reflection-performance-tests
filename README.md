# PHP5 / PHP7 Reflection Performance Tests
Simple and straight forward PHP 5 and PHP 7 reflection performance tests. Feel free to add more tests (pull request) for all reflection tests you know.

# Install and usage
Clone it, place it on your webserver, and open the test.php in your browser. Make sure that the `tmp` folder is writable from your php application. Don't use it in production enviroments, just for your safety.

# Why i've made this tests?
There often questions of how many performance impact reflections has. The question couldn't be clearly answered because it depends on how you use reflections. So here is a real world test that cover some common use cases for reflections. This test is not designed to show microsecond exact times. It is just made to get an idea of how many impact it can have if you use it in your applications and where the bottleneck is.
An example of heavy reflection use: Doc comments and defination in that comments that are required for the application to run properly (database properties, etc...).

# Overall results and my personal conclusion
* PHP 7 is almost twice as fast as PHP 5 in case of reflections - This does not directly indicate that reflections are faster on PHP7, the PHP7 core have just received a great optimization and all code will benefit from this.
* Basic reflections are quite fast - Reading methods and doc comments for 1000 classes cost just a few milliseconds. Parsing/Autoloading the classfiles does take a lot more time than the actual reflection mechanics. On our testsystem it takes about 300ms to load 1000 class files into memory (require/include/autoload) - And than just 1-5ms to use reflection parsing (doc comments, getMethods, etc...) on the same amount of classes. 
* Conclusion: Reflections are fast and in normal use cases you can ignore that performance impact. However, it is always recommended to only parse what is necessary. And, caching reflections doesn't give you any noticeable benefit on performance.

# Contribute
Please feel free to improve and add more tests.

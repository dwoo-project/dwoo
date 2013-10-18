<?php
namespace Dwoo\Exception;

/**
 * Class Xdebug
 * @package Dwoo\Exception
 */
class Xdebug {

	/**
	 * Xdebug constructor
	 */
	public function __construct() {

	}

	/**
	 * This function makes the debugger break on the specific line as if a normal file/line breakpoint was set on this line.
	 *
	 * @return void
	 */
	public function _break() {
		xdebug_break();
	}

	/**
	 * This function returns the name of the class from which the current function/method was called from.
	 * @return string
	 */
	public function callClass() {
		return xdebug_call_class();
	}

	/**
	 * This function returns the filename that contains the function/method that called the current function/method.
	 * @return string
	 */
	public function callFile () {
		return xdebug_call_file();
	}

	/**
	 * This function returns the name of the function/method from which the current function/method was called from.
	 * @return string
	 */
	public function callFunction () {
		return xdebug_call_function();
	}

	/**
	 * This function returns the line number that contains the function/method that called the current function/method.
	 * @return int
	 */
	public function callLine () {
		return xdebug_call_line();
	}

	/**
	 *
	 */
	public function clearAggrProfilingData () {
		return xdebug_clear_aggr_profiling_data();
	}

	/**
	 * This function displays structured information about one or more variables that includes its type, value and refcount information.
	 * Arrays are explored recursively with values.
	 * This function is implemented differently from PHP's debug_zval_dump() function in order to work around the problems
	 * that that function has because the variable itself is actually passed to the function.
	 * Xdebug's version is better as it uses the variable name to lookup the variable in the internal symbol table and
	 * accesses all the properties directly without having to deal with actually passing a variable to a function.
	 * The result is that the information that this function returns is much more accurate than PHP's own function
	 * for showing zval information.
	 * @return void
	 */
	public function debugZval () {
		xdebug_debug_zval();
	}

	/**
	 * This function displays structured information about one or more variables that includes its type,
	 * value and refcount information.
	 * Arrays are explored recursively with values.
	 * The difference with xdebug_debug_zval() is that the information is not displayed through a web server API layer,
	 * but directly shown on stdout (so that when you run it with apache in single process mode it ends up on the console).
	 * @return void
	 */
	public function debugZvalStdout () {
		xdebug_debug_zval_stdout();
	}

	/**
	 * Disable showing stack traces on error conditions.
	 * @return void
	 */
	public function disable () {
		xdebug_disable();
	}

	/**
	 *
	 */
	public function dumpAggrProfilingData () {
		xdebug_dump_aggr_profiling_data();
	}

	/**
	 * This function dumps the values of the elements of the super globals
	 * as specified with the xdebug.dump.* php.ini settings.
	 *
	 * @return void
	 */
	public function dumpSuperglobals () {
		xdebug_dump_superglobals();
	}

	/**
	 * Enable showing stack traces on error conditions.
	 * @return void
	 */
	public function enable () {
		xdebug_enable();
	}

	/**
	 * Returns a structure which contains information about which lines
	 * were executed in your script (including include files).
	 *
	 * @return array
	 */
	public function getCodeCoverage () {
		return xdebug_get_code_coverage();
	}

	/**
	 * Returns an array where each element is a variable name which is defined in the current scope.
	 * @return array
	 */
	public function getDeclaredVars () {
		return xdebug_get_declared_vars();
	}

	/**
	 * Returns the number of functions called, including constructors, desctructors and methods.
	 *
	 * @return int
	 */
	public function getFunctionCount () {
		return xdebug_get_function_count();
	}

	/**
	 * Displays the current function stack, in a similar way as what Xdebug would display in an error situation.
	 * @return array
	 */
	public function getFunctionStack () {
		return xdebug_get_function_stack();
	}

	/**
	 * Returns all the headers that are set with PHP's header() function,
	 * or any other header set internally within PHP (such as through setcookie()), as an array.
	 *
	 * @return array
	 */
	public function getHeaders() {
		return xdebug_get_headers();
	}

	/**
	 * Returns the name of the file which is used to save profile information to.
	 *
	 * @return string
	 */
	public function getProfilerFilename () {
		return xdebug_get_profiler_filename();
	}

	/**
	 * Returns the stack depth level.
	 * The main body of a script is level 0 and each include and/or function call adds one to the stack depth level.
	 * @return int
	 */
	public function getStackDepth () {
		return xdebug_get_stack_depth();
	}

	/**
	 * Returns the name of the file which is used to trace the output of this script too.
	 * This is useful when xdebug.auto_trace is enabled.
	 * @return string
	 */
	public function getTracefileName () {
		return xdebug_get_tracefile_name();
	}

	/**
	 * Return whether stack traces would be shown in case of an error or not.
	 * @return bool
	 */
	public function isEnabled() {
		return xdebug_is_enabled();
	}

	/**
	 * Returns the current amount of memory the script uses.
	 * Before PHP 5.2.1, this only works if PHP is compiled with --enable-memory-limit.
	 * From PHP 5.2.1 and later this function is always available.
	 *
	 * @return int
	 */
	public function memoryUsage () {
		return xdebug_memory_usage();
	}

	/**
	 * Returns the maximum amount of memory the script used until now.
	 * Before PHP 5.2.1, this only works if PHP is compiled with --enable-memory-limit.
	 * From PHP 5.2.1 and later this function is always available.
	 *
	 * @return int
	 */
	public function peakMemoryUsage () {
		return xdebug_peak_memory_usage();
	}

	/**
	 * Returns an array which resembles the stack trace up to this point.
	 * @return array
	 */
	public function printFunctionStack () {
		return xdebug_print_function_stack();
	}

	/**
	 * This function starts gathering the information for code coverage.
	 * The information that is collected consists of an two dimensional array with as primary index the executed filename and as secondary key the line number.
	 * The value in the elements represents the total number of execution units on this line have been executed.
	 * Options to this function are: XDEBUG_CC_UNUSED Enables scanning of code to figure out which line has executable code.
	 * XDEBUG_CC_DEAD_CODE Enables branch analyzes to figure out whether code can be executed.
	 *
	 * @return void
	 */
	public function startCodeCoverage () {
		xdebug_start_code_coverage();
	}

	/**
	 * Start tracing function calls from this point to the file in the trace_file parameter.
	 * If no filename is given, then the trace file will be placed in the directory as configured by the xdebug.trace_output_dir setting.
	 * In case a file name is given as first parameter, the name is relative to the current working directory.
	 * This current working directory might be different than you expect it to be, so please use an absolute path in case you specify a file name.
	 * Use the PHP function getcwd() to figure out what the current working directory is.
	 *
	 * @return void
	 */
	public function startTrace () {
		xdebug_start_trace();
	}

	/**
	 * This function stops collecting information, the information in memory will be destroyed.
	 * If you pass "false" as argument, then the code coverage information will not be destroyed so that you can resume
	 * the gathering of information with the xdebug_start_code_coverage() function again.
	 *
	 * @return void
	 */
	public function stopCodeCoverage () {
		xdebug_stop_code_coverage();
	}

	/**
	 * Stop tracing function calls and closes the trace file.
	 *
	 * @return void
	 */
	public function stopTrace () {
		xdebug_stop_trace();
	}

	/**
	 * Returns the current time index since the starting of the script in seconds.
	 *
	 * @return float
	 */
	public function timeIndex () {
		return xdebug_time_index();
	}

	/**
	 * This function displays structured information about one or more expressions that includes its type and value.
	 * Arrays are explored recursively with values.
	 * @return void
	 */
	public function varDump () {
		xdebug_var_dump();
	}
}
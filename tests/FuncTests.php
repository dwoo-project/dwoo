<?php

require_once 'Dwoo/Compiler.php';

class FuncTests extends PHPUnit_Framework_TestCase
{
	protected $compiler;
	protected $dwoo;

	public function __construct()
	{
		// extend this class and override this in your constructor to test a modded compiler
		$this->compiler = new Dwoo_Compiler();
		$this->dwoo = new Dwoo(DWOO_COMPILE_DIR, DWOO_CACHE_DIR);
	}

	public function testAssign()
	{
		// test simple assign
		$tpl = new Dwoo_Template_String('{assign "bar" foo}{$foo}');
		$tpl->forceCompilation();

		$tpl2 = new Dwoo_Template_String('{assign baz foo}{$foo}');
		$tpl2->forceCompilation();

		$this->assertEquals('barbaz', $this->dwoo->get($tpl, array(), $this->compiler) . $this->dwoo->get($tpl2, array(), $this->compiler));

		// test array assignation with function call
		$tpl = new Dwoo_Template_String('{assign reverse($foo) foo}{foreach $foo val}{$val}{/foreach}');
		$tpl->forceCompilation();

		$this->assertEquals('321', $this->dwoo->get($tpl, array('foo'=>array(1,2,3)), $this->compiler));
	}

	public function testCapitalize()
	{
		$tpl = new Dwoo_Template_String('{capitalize "hello world 1st"}-{capitalize "hello world 1st" true}');
		$tpl->forceCompilation();

		$this->assertEquals('Hello World 1st-Hello World 1St', $this->dwoo->get($tpl, array(), $this->compiler));
	}

	public function testCat()
	{
		$tpl = new Dwoo_Template_String('{cat 3 bar "FOO"}');
		$tpl->forceCompilation();

		$this->assertEquals('3barFOO', $this->dwoo->get($tpl, array(), $this->compiler));
	}

	public function testCounter()
	{
		$tpl = new Dwoo_Template_String('{counter start=0 skip=2}{counter}{counter}{counter}');
		$tpl->forceCompilation();

		$this->assertEquals('0246', $this->dwoo->get($tpl, array(), $this->compiler));

		$tpl = new Dwoo_Template_String('{counter start=0 skip=2 assign=foo}{counter}{counter}{counter}{$foo}');
		$tpl->forceCompilation();

		$this->assertEquals('6', $this->dwoo->get($tpl, array(), $this->compiler));

		$tpl = new Dwoo_Template_String('{counter start=0 skip=2 assign=foo direction=down}{counter}{counter}{counter}{$foo}');
		$tpl->forceCompilation();

		$this->assertEquals('-6', $this->dwoo->get($tpl, array(), $this->compiler));

		$tpl = new Dwoo_Template_String('{counter start=0 skip=3 print=false}{counter}{counter}{counter}{counter print=true}');
		$tpl->forceCompilation();

		$this->assertEquals('12', $this->dwoo->get($tpl, array(), $this->compiler));
	}

	public function testCountChars()
	{
		$tpl = new Dwoo_Template_String('{count_characters "hello world"}{count_characters "hello world" true}');
		$tpl->forceCompilation();

		$this->assertEquals('1011', $this->dwoo->get($tpl, array(), $this->compiler));
	}

	public function testCountParagraphs()
	{
		$tpl = new Dwoo_Template_String('{count_paragraphs $foo}');
		$tpl->forceCompilation();

		$this->assertEquals('4', $this->dwoo->get($tpl, array('foo'=>"amdslfk smdkmsfd\nmsldkfmsldsdfml\nmlskfmlsdksdf\nmklsdspo"), $this->compiler));
	}

	public function testCountSentences()
	{
		$tpl = new Dwoo_Template_String('{count_sentences $foo}');
		$tpl->forceCompilation();

		$this->assertEquals('4', $this->dwoo->get($tpl, array('foo'=>"amdslfk smdkmsfd.\nmsldkfmsldsdfml. sdfsml\nmlskfmlsdksdf... mklsdspo."), $this->compiler));
	}

	public function testCountWords()
	{
		$tpl = new Dwoo_Template_String('{count_words $foo}');
		$tpl->forceCompilation();

		$this->assertEquals('5', $this->dwoo->get($tpl, array('foo'=>"sfsdf.smdf\nmkoep\tsdlk sdfsdf"), $this->compiler));
	}

	public function testCycle()
	{
		$tpl = new Dwoo_Template_String('{cycle "hoy" array(foo,bar) }{cycle hoy}{cycle hoy}{cycle name=hoy print=false}{cycle name="hoy"}{cycle name=hoy reset=true advance=false}{cycle name="hoy" advance=true}');
		$tpl->forceCompilation();

		$this->assertEquals('foobarfoofoofoofoo', $this->dwoo->get($tpl, array('foo'=>"sfsdf.smdf\nmkoep\tsdlk sdfsdf"), $this->compiler));
	}

	public function testDateFormat()
	{
		$tpl = new Dwoo_Template_String('{date_format $dwoo.now "%Y%H:%M:%S"}{date_format $foo "%Y%H:%M:%S" "one hour ago"}{date_format ""}');
		$tpl->forceCompilation();

		$this->assertEquals(strftime("%Y%H:%M:%S", $_SERVER['REQUEST_TIME']).strftime('%Y%H:%M:%S', strtotime("one hour ago")), $this->dwoo->get($tpl, array('foo'=>''), $this->compiler));
	}

	public function testDefault()
	{
		$tpl = new Dwoo_Template_String('{default $foo bar}{default $foo2 bar}{default $foo3 bar}');
		$tpl->forceCompilation();

		$this->assertEquals("barbarfoo3", $this->dwoo->get($tpl, array('foo2'=>"", 'foo3'=>"foo3"), $this->compiler));
	}

	public function testDo()
	{
		$tpl = new Dwoo_Template_String('{do assign($foo bar)}{do $bar}{$bar}');
		$tpl->forceCompilation();

		$this->assertEquals("moo", $this->dwoo->get($tpl, array('foo'=>"moo"), $this->compiler));
	}

	public function testDoEmpty()
	{
		$tpl = new Dwoo_Template_String('{do}');
		$tpl->forceCompilation();

		$this->assertEquals("", $this->dwoo->get($tpl, array('foo'=>"moo"), $this->compiler));
	}

	public function testEol()
	{
		$tpl = new Dwoo_Template_String('{eol}');
		$tpl->forceCompilation();

		$this->assertEquals(PHP_EOL, $this->dwoo->get($tpl, array(), $this->compiler));
	}

	public function testEscape()
	{
		$tpl = new Dwoo_Template_String('{escape $foo}');
		$tpl->forceCompilation();

		$this->assertEquals("&quot;", $this->dwoo->get($tpl, array('foo'=>'"'), $this->compiler));

		$tpl = new Dwoo_Template_String('{escape $foo htmlall}');
		$tpl->forceCompilation();

		$this->assertEquals("&eacute;", $this->dwoo->get($tpl, array('foo'=>'é'), $this->compiler));

		$tpl = new Dwoo_Template_String('{escape $foo htmlall iso-8859-1}');
		$tpl->forceCompilation();

		$this->assertEquals("&uuml;", $this->dwoo->get($tpl, array('foo'=>utf8_decode('ü')), $this->compiler));

		$tpl = new Dwoo_Template_String('{escape $foo url}');
		$tpl->forceCompilation();

		$this->assertEquals(rawurlencode(':#?/'), $this->dwoo->get($tpl, array('foo'=>':#?/'), $this->compiler));

		$tpl = new Dwoo_Template_String('{escape $foo quotes}');
		$tpl->forceCompilation();

		$this->assertEquals("\\'", $this->dwoo->get($tpl, array('foo'=>'\''), $this->compiler));

		$tpl = new Dwoo_Template_String('{escape $foo urlpathinfo}');
		$tpl->forceCompilation();

		$this->assertEquals(rawurlencode(':#?').'/', $this->dwoo->get($tpl, array('foo'=>':#?/'), $this->compiler));

		$tpl = new Dwoo_Template_String('{escape $foo hex}');
		$tpl->forceCompilation();

		$this->assertEquals('%0a', $this->dwoo->get($tpl, array('foo'=>chr(10)), $this->compiler));

		$tpl = new Dwoo_Template_String('{escape $foo hexentity}');
		$tpl->forceCompilation();

		$this->assertEquals('&#x0a;', $this->dwoo->get($tpl, array('foo'=>chr(10)), $this->compiler));

		$tpl = new Dwoo_Template_String('{escape $foo javascript}');
		$tpl->forceCompilation();

		$this->assertEquals('\\\\n', $this->dwoo->get($tpl, array('foo'=>'\\n'), $this->compiler));

		$tpl = new Dwoo_Template_String('{escape $foo mail}');
		$tpl->forceCompilation();

		$this->assertEquals('test&nbsp;(AT)&nbsp;foo&nbsp;(DOT)&nbsp;bar', $this->dwoo->get($tpl, array('foo'=>'test@foo.bar'), $this->compiler));
	}

	public function testEval()
	{
		$tpl = new Dwoo_Template_String('{eval $foo}{assign "baz" test}{eval $foo bar}+{$bar}');
		$tpl->forceCompilation();

		$this->assertEquals("moo+baz", $this->dwoo->get($tpl, array('foo'=>'{$test}', 'test'=>'moo'), $this->compiler));
	}

	public function testExtends()
	{
		$tpl = new Dwoo_Template_File(TEST_DIRECTORY.'/resources/extends/sub/test.html');
		$tpl->forceCompilation();

		$this->assertEquals("<html>ThirdLevel</html>", $this->dwoo->get($tpl, array(), $this->compiler));
	}

	public function testExtendsVariation2()
	{
		$tpl = new Dwoo_Template_File(TEST_DIRECTORY.'/resources/extends/root.html');
		$tpl->forceCompilation();

		$this->assertEquals("<html>Root</html>", $this->dwoo->get($tpl, array(), $this->compiler));
	}

	public function testFetch()
	{
		$tpl = new Dwoo_Template_String('{fetch file="'.TEST_DIRECTORY.'/resources/test.html"}');
		$tpl->forceCompilation();

		$this->assertEquals('{$foo}{$bar}', $this->dwoo->get($tpl, array(), $this->compiler));
	}

	public function testInclude()
	{
		$tpl = new Dwoo_Template_String('{include file=\'file:'.TEST_DIRECTORY.'/resources/test.html\' foo=$a bar=$b}');
		$tpl->forceCompilation();

		$this->assertEquals("AB", $this->dwoo->get($tpl, array('a'=>'A', 'b'=>'B')));

		$tpl = new Dwoo_Template_String('{include file=\'file:'.TEST_DIRECTORY.'/resources/test.html\'}');
		$tpl->forceCompilation();

		$this->assertEquals("ab", $this->dwoo->get($tpl, array('foo'=>'a', 'bar'=>'b'), $this->compiler));

		$tpl = new Dwoo_Template_File(TEST_DIRECTORY.'/resources/inctest.html');
		$tpl->forceCompilation();

		$this->assertEquals("34", $this->dwoo->get($tpl, array(), $this->compiler));
	}

	public function testIncludeString()
	{
		$tpl = new Dwoo_Template_String('{include file=$tpl foo=$a bar=$b}');
		$tpl->forceCompilation();

		$this->assertEquals("AB", $this->dwoo->get($tpl, array('a'=>'A', 'b'=>'B', 'tpl'=>'{$a}{$b}')));
	}

	public function testIncludeParent()
	{
		$tpl = new Dwoo_Template_File(TEST_DIRECTORY.'/resources/subfolder/inctest.html');
		$tpl->forceCompilation();

		$this->assertEquals("43", $this->dwoo->get($tpl, array(), $this->compiler));
	}

	public function testIndent()
	{
		$tpl = new Dwoo_Template_String('{indent $foo 6 "-"}');
		$tpl->forceCompilation();

		$this->assertEquals("------FOO\n------BAR", $this->dwoo->get($tpl, array('foo'=>"FOO\nBAR"), $this->compiler));
	}

	public function testIsset()
	{
		$tpl = new Dwoo_Template_String('{if isset($foo)}set{else}not set{/if}');
		$tpl->forceCompilation();
		$this->assertEquals("not set", $this->dwoo->get($tpl, array(), $this->compiler));
		$this->assertEquals("set", $this->dwoo->get($tpl, array('foo'=>'a'), $this->compiler));
		$this->assertEquals("set", $this->dwoo->get($tpl, array('foo'=>''), $this->compiler));
	}

	public function testLower()
	{
		$tpl = new Dwoo_Template_String('{lower "FOO"}');
		$tpl->forceCompilation();

		$this->assertEquals("foo", $this->dwoo->get($tpl, array(), $this->compiler));
	}

	public function testMailto()
	{
		$tpl = new Dwoo_Template_String('{mailto address="me@example.com" encode="jschar"}');
		$tpl->forceCompilation();

		$this->assertEquals('<script type="text/javascript">'."\n".'<!--'."\n".'document.write(String.fromCharCode(60,97,32,104,114,101,102,61,34,109,97,105,108,116,111,58,109,101,64,101,120,97,109,112,108,101,46,99,111,109,34,32,62,109,101,64,101,120,97,109,112,108,101,46,99,111,109,60,47,97,62));'."\n".'-->'."\n".'</script>'."\n", $this->dwoo->get($tpl, array(), $this->compiler));

		$tpl = new Dwoo_Template_String('{mailto address="me@example.com" text="send me some mail"}');
		$tpl->forceCompilation();

		$this->assertEquals('<a href="mailto:me@example.com" >send me some mail</a>', $this->dwoo->get($tpl, array(), $this->compiler));

		$tpl = new Dwoo_Template_String('{mailto address="me@example.com" encode="javascript"}');
		$tpl->forceCompilation();

		$this->assertEquals('<script type="text/javascript">eval(unescape(\'%64%6f%63%75%6d%65%6e%74%2e%77%72%69%74%65%28%27%3c%61%20%68%72%65%66%3d%22%6d%61%69%6c%74%6f%3a%6d%65%40%65%78%61%6d%70%6c%65%2e%63%6f%6d%22%20%3e%6d%65%40%65%78%61%6d%70%6c%65%2e%63%6f%6d%3c%2f%61%3e%27%29%3b\'));</script>', $this->dwoo->get($tpl, array(), $this->compiler));

		$tpl = new Dwoo_Template_String('{mailto address="me@example.com" encode="hex"}');
		$tpl->forceCompilation();

		$this->assertEquals('<a href="&#109;&#97;&#105;&#108;&#116;&#111;&#58;%6d%65@%65%78%61%6d%70%6c%65.%63%6f%6d" >&#x6d&#x65&#x40&#x65&#x78&#x61&#x6d&#x70&#x6c&#x65&#x2e&#x63&#x6f&#x6d</a>', $this->dwoo->get($tpl, array(), $this->compiler));

		$tpl = new Dwoo_Template_String('{mailto address="me@example.com" subject="Hello to you!" cc="you@example.com,they@example.com" extra=\'class="email"\'}');
		$tpl->forceCompilation();

		$this->assertEquals('<a href="mailto:me@example.com?subject=Hello%20to%20you%21&cc=you%40example.com%2Cthey%40example.com" class="email">me@example.com</a>', $this->dwoo->get($tpl, array(), $this->compiler));
	}

	public function testMath()
	{
		$tpl = new Dwoo_Template_String('{math equation="3+5+a+b" a="100" b="20"}');
		$tpl->forceCompilation();

		$this->assertEquals((string)(3+5+100+20), $this->dwoo->get($tpl, array(), $this->compiler));

		$tpl = new Dwoo_Template_String('{math equation="3+5+`$a`+b" b="20"}');
		$tpl->forceCompilation();

		$this->assertEquals((string)(3+5+100+20), $this->dwoo->get($tpl, array('a'=>100), $this->compiler));

		$tpl = new Dwoo_Template_String('{math equation="3+5+cos(a)" a="1"}');
		$tpl->forceCompilation();

		$this->assertEquals((string)(3+5+cos(1)), $this->dwoo->get($tpl, array(), $this->compiler));

		$tpl = new Dwoo_Template_String('{math equation="3+5+(cos(a) + max(3,2))" a="1"}');
		$tpl->forceCompilation();

		$this->assertEquals((string)(3+5+(cos(1) + max(3,2))), $this->dwoo->get($tpl, array(), $this->compiler));

		$tpl = new Dwoo_Template_String('{math equation="3+5+(cos(a) + max(3,2))" a="1" assign="foo"}-{$foo}');
		$tpl->forceCompilation();

		$this->assertEquals('-'.(3+5+(cos(1) + max(3,2))), $this->dwoo->get($tpl, array(), $this->compiler));
	}

	public function testNl2br()
	{
		$tpl = new Dwoo_Template_String('{nl2br "f
a"}');
		$tpl->forceCompilation();

		$this->assertEquals("f<br />\na", $this->dwoo->get($tpl, array(), $this->compiler));
	}

	public function testRegexReplace()
	{
		$tpl = new Dwoo_Template_String('{regex_replace "FOOMOO" "/OO\$/" "L"}');
		$tpl->forceCompilation();

		$this->assertEquals("FOOML", $this->dwoo->get($tpl, array(), $this->compiler));

		$tpl = new Dwoo_Template_String('{regex_replace "FOOMOO" "/OO\$/e" "die"}');
		$tpl->forceCompilation();

		$this->assertEquals("FOOMdie", $this->dwoo->get($tpl, array(), $this->compiler));
	}

	public function testReplace()
	{
		$tpl = new Dwoo_Template_String('{replace "FOOMOO" "OO" "L"}');
		$tpl->forceCompilation();

		$this->assertEquals("FLML", $this->dwoo->get($tpl, array(), $this->compiler));

		$tpl = new Dwoo_Template_String('{replace "FOOMOO" "oo" "l" off}');
		$tpl->forceCompilation();

		$this->assertEquals("FlMl", $this->dwoo->get($tpl, array(), $this->compiler));
	}

	public function testReverse()
	{
		$tpl = new Dwoo_Template_String('{reverse "abc"}');
		$tpl->forceCompilation();

		$this->assertEquals("cba", $this->dwoo->get($tpl, array(), $this->compiler));

		$tpl = new Dwoo_Template_String('{foreach reverse(array(a,b,c)) foo}{$foo}{/foreach}');
		$tpl->forceCompilation();

		$this->assertEquals("cba", $this->dwoo->get($tpl, array(), $this->compiler));
	}

	public function testSpacify()
	{
		$tpl = new Dwoo_Template_String('{spacify "ABC" "+"}');
		$tpl->forceCompilation();

		$this->assertEquals("A+B+C", $this->dwoo->get($tpl, array(), $this->compiler));
	}

	public function testStringFormat()
	{
		$tpl = new Dwoo_Template_String('{string_format 53.3942 "%.2f"}');
		$tpl->forceCompilation();

		$this->assertEquals("53.39", $this->dwoo->get($tpl, array(), $this->compiler));
	}

	public function testStripTags()
	{
		$tpl = new Dwoo_Template_String('{strip_tags "<a href=\'foo\'>test</a>test"}');
		$tpl->forceCompilation();

		$this->assertEquals(" test test", $this->dwoo->get($tpl, array(), $this->compiler));

		$tpl = new Dwoo_Template_String('{strip_tags "<a href=\'foo\'>test</a>test" false}');
		$tpl->forceCompilation();

		$this->assertEquals("testtest", $this->dwoo->get($tpl, array(), $this->compiler));
	}

	public function testWhitespace()
	{
		$tpl = new Dwoo_Template_String('{whitespace "a		b	 c   		   d"}');
		$tpl->forceCompilation();

		$this->assertEquals("a b c d", $this->dwoo->get($tpl, array(), $this->compiler));
	}

	public function testTif()
	{
		$tpl = new Dwoo_Template_String('{tif $a && $b && 3==3 ? foo : bar}');
		$tpl->forceCompilation();

		$this->assertEquals("foo", $this->dwoo->get($tpl, array('a'=>true, 'b'=>3), $this->compiler));

		$tpl = new Dwoo_Template_String('{tif $a && $b && 3==3 foo bar}');
		$tpl->forceCompilation();

		$this->assertEquals("bar", $this->dwoo->get($tpl, array('a'=>true, 'b'=>0), $this->compiler));

		$tpl = new Dwoo_Template_String('{tif $a foo bar}');
		$tpl->forceCompilation();

		$this->assertEquals("foo", $this->dwoo->get($tpl, array('a'=>true, 'b'=>0), $this->compiler));

		$tpl = new Dwoo_Template_String('{tif $a?"foo":"bar"}');
		$tpl->forceCompilation();

		$this->assertEquals("foo", $this->dwoo->get($tpl, array('a'=>true, 'b'=>0), $this->compiler));

		$tpl = new Dwoo_Template_String('{tif $a?foo:bar}');
		$tpl->forceCompilation();

		$this->assertEquals("foo", $this->dwoo->get($tpl, array('a'=>true, 'b'=>0), $this->compiler));

		$tpl = new Dwoo_Template_String('{tif $a ? foo}');
		$tpl->forceCompilation();

		$this->assertEquals("foo", $this->dwoo->get($tpl, array('a'=>true, 'b'=>0), $this->compiler));

		$tpl = new Dwoo_Template_String('{tif $a foo}');
		$tpl->forceCompilation();

		$this->assertEquals("foo", $this->dwoo->get($tpl, array('a'=>true, 'b'=>0), $this->compiler));

		$tpl = new Dwoo_Template_String('{tif $a ?: foo}');
		$tpl->forceCompilation();

		$this->assertEquals("1", $this->dwoo->get($tpl, array('a'=>true, 'b'=>0), $this->compiler));
	}

	public function testTruncate()
	{
		$tpl = new Dwoo_Template_String('{truncate "abcdefghijklmnopqrstuvwxyz" 20 "..." true}');
		$tpl->forceCompilation();

		$this->assertEquals("abcdefghijklmnopq...", $this->dwoo->get($tpl, array(), $this->compiler));

		$tpl = new Dwoo_Template_String('{truncate "abcdefghijklmnopqrstuvwxyz" 20 "..." true true}');
		$tpl->forceCompilation();

		$this->assertEquals("abcdefghi...stuvwxyz", $this->dwoo->get($tpl, array(), $this->compiler));
	}

	public function testUpper()
	{
		$tpl = new Dwoo_Template_String('{upper "foo"}');
		$tpl->forceCompilation();

		$this->assertEquals("FOO", $this->dwoo->get($tpl, array(), $this->compiler));
	}

	public function testWordwrap()
	{
		$tpl = new Dwoo_Template_String('{wordwrap "abcdefghijklmnopqrstuvwxyz" 8 "\n" true}');
		$tpl->forceCompilation();

		$this->assertEquals("abcdefgh\nijklmnop\nqrstuvwx\nyz", $this->dwoo->get($tpl, array(), $this->compiler));
	}
}

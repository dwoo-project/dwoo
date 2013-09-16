{$arr=array( 'sub' => array( 'foo' => 'bar' ) )}
{$url='example.org'}
{with $arr.sub}
{$foo} / {$_root.arr.sub.foo} / {$_parent.foo}
{$_root.url} / {$_parent._parent.url}
{$dwoo.version}
{/with}
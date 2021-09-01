$(".logoutbt").click(function(e) {
			$.showfloatdiv({
				txt: '正在退出...',
				cssname: 'loading',
				isajax: 1,
				succdo: function(r) {
					setTimeout("$.refresh('index.php?s=User-Center-login')",500);
				}
			});
			return false;
});	
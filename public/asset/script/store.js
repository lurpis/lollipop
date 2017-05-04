/*
* @Author: hangbale
* @Date:   2016-09-17 21:45:16
* @Last Modified by:   hangbale
* @Last Modified time: 2016-10-11 14:51:10
*/

window.app.module['/store'] = function() {
	var pay = {
		channel : '',
		chargeId : null,
		// {
		// 	'common' : ['alipay_wap','wx_pub_qr'],
		// 	'weixin' : ['wx_pub']
		// },
		switchChannel : function () {
			if(window.pingxx.borwserType === 'weixin'){
				this.channel = 'wx_pub';
				this.createPay();
			}else{

			}
		},
		init : function () {
			var self = this;
			$('#buy-submit').on('click',function () {
				self.createPay();
			})
		},
		createPay : function () {
			var datas;
			if(window.pingxx.browserType === 'weixin'){
				datas = {
					channel : $('#channel-choice').find('input[type="radio"]:checked').val(),
					type : Choice.type,
					time : Choice.time,
					open_id : window.pingxx.openId
				}
			}else{
				datas = {
					channel : $('#channel-choice').find('input[type="radio"]:checked').val(),
					type : Choice.type,
					time : Choice.time,
				}
			}
			$.ajax({
				url: '/api/buy',
				type: 'GET',
				data: datas
			})
			.done(function(res) {
				if(res.state === 'success'){
					pay.chargeId = res.data.charge.id;
					if((pay.channel === 'alipay_qr') || (pay.channel === 'wx_pub_qr')){
						generateQr(res.data.qr);
					}else{
						pingpp.createPayment(res.data.charge, function(result, err) {
							if (result == "success") {
								ui.modal('微信支付')
							} else if (result == "fail") {
								ui.modal('微信支付失败');
							} else if (result == "cancel") {
								
							}
						});
					}
					watchPayStatus();
				}
			})
			.fail(function() {
				ui.modal('操作失败');
			})
			
		}
	}
	if(window.pingxx.browserType === 'common'){
		pay.channel = 'alipay_qr';
		$('#channel-common').show();
		$('#channel-common').find('input').eq(0).attr('checked','checked');
	}else{
		if(!window.pingxx.getToken('open_id')){
			getLogin();
		}else{
			window.pingxx.openId = window.pingxx.getToken('open_id');
		}
		pay.channel = 'wx_pub';
		$('#channel-mobile').show();
		$('#channel-type-wepub').attr('checked','checked');
	}
	var prompt = function (opt) {
					var div,wrap;
					$('.prompt-wrap').remove();
					div = document.createElement('div');
					div.setAttribute('class','prompt-wrap');
					wrap = document.createElement('div');
					wrap.setAttribute('class','prompt-body');

					var header = document.createElement('div');
					header.setAttribute('class','prompt-header');
					header.innerHTML = '使用提示';
					var footer = document.createElement('div');
					footer.setAttribute('class','prompt-footer');
					footer.innerHTML = '<div class="input-group">'
					+'<a class="btn" href="javascript:;" id="useNow">立即使用</a>'
					+'<a class="btn" href="javascript:;" id="cancelStore">关闭</a>'
					+'</div>';
					div.appendChild(header);
					div.appendChild(wrap);
					
					div.appendChild(footer);
					document.body.appendChild(div);

					$('#cancelStore').on('click',function () {
						$(div).fadeOut();
					})
						
					if(opt.title){
						$('.prompt-header').html(opt.title);
					}
					if(!opt.showBtn){
						$('#useNow').hide();
					}else{
						$('#useNow').show();
					}
					if(opt.btnTxt){
						$('#useNow').html(opt.btnTxt)
					}
					if(opt.btnFn){
						$('#useNow').on('click',opt.btnFn);
					}
					wrap.innerHTML = opt.tpl;
					$(div).fadeIn();
					
				}
	function generateQr(code) {
		var t = '<img class="qr-img" src="' + code + '">';
		//$(window).scrollTop(0);
		prompt({tpl : t,showBtn:false,title:'扫码支付'})
	}
	$('#goto-index').on('click',function () {
		getUrl('/index');
	})
	function storeKey(rec) {
		var c = getCookie('the_keys');
		if(c){
			var keys = JSON.parse(c);
			keys.push(rec);
			setCookie('the_keys', JSON.stringify(keys), 7);
		}else{
			setCookie('the_keys', JSON.stringify([rec]), 7);
		}
	}
	function getLogin() {
		window.location.href = '/api/login/wx_base';
	}
	function watchPayStatus() {
		var i = 0,data = null;
		var req = function () {
			i++;
			if(i % 5 === 0){
				data = {
					force : 1
				}
			}else{
				data = null;
			}
			$.ajax({
				url: '/api/retrieve/'+pay.chargeId,
				type: 'GET',
				data: data,
			})
			.done(function(res) {
				if(res.state === 'success'){
					var t = '<p><span class="item-title">激活码:&emsp;</span>'+res.data+'</p>';
					t += '<p>请您牢记您的激活码,我们将为您保存最近七天的购买记录</p>'
					prompt({
						tpl:t,
						title:'支付结果',
						showBtn:true,
						btnTxt : '立即使用',
						btnFn : function () {
							getUrl('/index');
							$('.prompt-wrap').fadeOut();
							setTimeout(function () {
								$('#toggle-inputKey').show();
								$('#userKey').val(res.data);
							},1000)
						}
					});
					var now = new Date();
					storeKey({
						date : +now.getFullYear()+'-'+(now.getMonth()+1)+'-'+now.getDate(),
						key : res.data
					});
					clearInterval(loop);
				}
			})
			.fail(function() {
				ui.modal('获取支付结果失败,请联系客服');
				clearInterval(loop);
			})
		}
		var loop = setInterval(req,1000);
		
	}
	scale = function(btn, bar, showbar, callback) {
		this.btn = document.getElementById(btn);
		this.bar = document.getElementById(bar);
		this.step = document.getElementById(showbar)
		this.callback = callback;
		this.init();
	};
	scale.prototype = {
		init: function() {
			var _this = this;

			_this.btn.onmousedown = function(e) {
				var mouseX = (e || window.event).clientX;
				var btnX = this.offsetLeft;
				var max = _this.bar.offsetWidth - this.offsetWidth;
				document.onmousemove = function(e) {
					var thisX = (e || window.event).clientX;
					var to = Math.min(max, Math.max(1,thisX - mouseX+btnX));
					_this.btn.style.left = to + 'px';
					_this.step.style.width = to + 'px';
					_this.ondrag(Math.max(0, to / max), to);
					window.getSelection ? window.getSelection().removeAllRanges() : document.selection.empty();
				};
				document.onmouseup = new Function('this.onmousemove=null');
			};
		
		},
		ondrag: function (percent,nowPos) {
			this.callback(percent,nowPos)
		}
	};
	var Choice = {
		type : null,
		time : null,
		switchType : function (v) {
			this.type = v;
			choiceUi.setTypeDom();
		},
		calcPrice :function () {
			var map = {
				day : 2,
				week : 13,
				month : 50,
				year : 550
			}
			if(this.type && this.time){
				return map[this.type] * this.time/100;
			}else{
				return 0;
			}
		},
		setPrice : function (time) {
			if(time){
				Choice.time = time;
			}
			document.getElementById('buy-price').innerHTML = this.calcPrice();
		},
		init : function (type,time) {
			this.type = type;
			this.time = time;
			choiceUi.setTypeDom();
			this.setPrice(1);
		}
	}
	var choiceUi = {
		typeDoms : $('#choice-type').find('a'),
		setTypeDom : function () {
			for (var i = this.typeDoms.length - 1; i >= 0; i--) {
				if(this.typeDoms.eq(i).attr('data-card-type') === Choice.type){
					this.typeDoms.eq(i).addClass('active');
				}else{
					this.typeDoms.eq(i).removeClass('active');
				}
			}
			this.showTimeDom();
		},
		showTimeDom : function () {
			var map = {
				day : {
					dom : document.getElementById('choice-type-day'),
					clear : function () {
						document.getElementById('drag-btn').style.left = '1px';
						document.getElementById('drag-showbar').style.width = '1px';
						document.getElementById('dragbar-wrap').style.display = 'block';
						$('#choice-type-day').find('input').eq(0).val(1);
						$('#choice-type-day').find('span').eq(0).html('天');
						Choice.setPrice(1);
					}
				},
				week : {
					dom : document.getElementById('choice-type-day'),
					clear : function () {
						document.getElementById('drag-btn').style.left = '1px';
						document.getElementById('drag-showbar').style.width = '1px';
						document.getElementById('dragbar-wrap').style.display = 'block';
						$('#choice-type-day').find('input').eq(0).val(1);
						$('#choice-type-day').find('span').eq(0).html('周');
						Choice.setPrice(1);
					}
				},
				month : {
					dom : document.getElementById('choice-type-month'),
					clear : function () {
						document.getElementById('dragbar-wrap').style.display = 'none';
						document.getElementById('choice-type-month').selectedIndex = 0;
						Choice.setPrice(1);
					}
				},
				year : {
					dom : document.getElementById('choice-type-year'),
					clear : function () {
						document.getElementById('dragbar-wrap').style.display = 'none';
						document.getElementById('choice-type-year').selectedIndex = 0;
						Choice.setPrice(1);
					}
				}
			};

			for(var key in map){
				if(key === Choice.type){
					map[key].dom.style.display = 'block';
					map[key].clear();
				}else{
					if(key === 'week') continue;
					map[key].dom.style.display = 'none';
				}
			}
		}
	}
	Choice.init('month',1);
	pay.init();
	$('#choice-type').on('click',function (e) {
		if(e.target.nodeName === 'A'){
			Choice.switchType(e.target.getAttribute('data-card-type'));
		}
	});
	$('#choice-type-month,#choice-type-year').on('change',function () {
		Choice.setPrice($(this).find('option:selected').val());
	})
	$('#dragbar-input').on('input',function () {
		var map = {
			day : 364,
			week : 52
		}
		if(/^\d{1,3}$/.test(this.value)){
			this.value = Math.min(map[Choice.type],Math.max(0,this.value));
			
		}else{
			this.value=0;
		}
		Choice.setPrice(this.value);
	})
	$('#dragbar-input').on('blur',function () {
		if(this.value == 0){
			this.value = 1;
			Choice.setPrice(this.value);
		}
	})

	new scale('drag-btn', 'dragbar-wrap' ,'drag-showbar', function (percent,nowPos) {
		var map = {
				day : 364,
				week : 52,
			}
		Choice.time = Math.max(1,Math.round(percent*map[Choice.type]));
		$('#choice-type-day').find('input').eq(0).val(Choice.time);
		Choice.setPrice();
	});
}

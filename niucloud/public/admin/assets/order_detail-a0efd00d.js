import{d as J,r as k,a as K,u as Q,F as U,c as d,e as m,f as e,t,h as o,H as s,a4 as R,I as b,w as n,g as i,P as u,z as X,a5 as Z,O as ee,a1 as te,N as L,an as oe,n as se,b6 as ae,b7 as le,p as ne,aq as ie,ar as re,af as de,q as ce,a9 as _e,s as pe,v as me,_ as ue}from"./index-4c6d44e8.js";/* empty css                   *//* empty css                *//* empty css                *//* empty css                 *//* empty css                    */import"./el-tooltip-4ed993c7.js";/* empty css                  *//* empty css                     *//* empty css                        *//* empty css               *//* empty css                  *//* empty css               *//* empty css               */import"./el-form-item-4ed993c7.js";import{u as he,v as be}from"./bill-39486054.js";import{g as fe}from"./config-02170d18.js";const C=f=>(pe("data-v-d850be14"),f=f(),me(),f),xe={class:"main-container"},ve={class:"detail-head"},ye=C(()=>e("span",{class:"iconfont iconxiangzuojiantou !text-xs"},null,-1)),ge={class:"ml-[1px]"},we=C(()=>e("span",{class:"adorn"},"|",-1)),ke={class:"right"},Ce={class:"panel-title"},Te={class:"input-width"},ze={class:"input-width"},De={class:"input-width"},Ee={class:"input-width"},je={class:"input-width"},Ie={class:"input-width"},Ne={class:"input-width"},Be={class:"input-width"},Se={class:"input-width"},Oe={class:"panel-title"},Fe={class:"mb-[20px]"},Re={class:"ml-[30px] text-[14px] mr-[20px]"},Le={class:"text-[14px]"},Me={class:"flex mt-[10px] justify-end"},qe={class:"panel-title"},Pe={class:"py-[12px] px-[16px] border-b border-color flex justify-end"},Ve={class:"w-[310px] flex flex-col text-right"},We={class:"flex mb-[10px]"},He={class:"text-base flex-1"},$e={class:"text-base flex-1 pl-[30px]"},Ge={class:"flex mb-[10px]"},Ye={class:"text-base flex-1"},Ae={class:"text-base flex-1 pl-[30px]"},Je={class:"flex"},Ke={class:"text-base flex-1"},Qe={class:"text-base flex-1 pl-[30px] text-[red]"},Ue={class:"print-section"},Xe={class:"print-header"},Ze={class:"text-center font-bold",style:{"font-size":"24px"}},et={class:"print-info"},tt={class:"flex justify-between"},ot={class:"font-bold",style:{"font-size":"16px"}},st={class:"flex justify-between"},at={class:"flex justify-between"},lt={key:0},nt={key:1},it={class:"print-detail"},rt={class:"w-full border-collapse"},dt={class:"border border-gray-300 p-1"},ct={class:"border border-gray-300 p-1"},_t={class:"border border-gray-300 p-1"},pt={class:"border border-gray-300 p-1"},mt={class:"border border-gray-300 p-1"},ut={class:"border border-gray-300 p-1"},ht={class:"border border-gray-300 p-1"},bt={class:"border border-gray-300 p-1"},ft={class:"text-right mt-2"},xt={class:"font-bold"},vt={class:"mt-8 text-[12px] absolute bottom-[5mm] w-[calc(100%-10mm)]"},yt={class:"flex items-center pt-5 border-t border-dashed border-gray-300"},gt={class:"flex items-center"},wt=["src"],kt=C(()=>e("div",{style:{"margin-left":"10px"}},[e("div",{class:"font-bold text-[13px] ml-2"},"扫一扫,免排队"),e("div",{class:"text-[11px] text-gray-500 ml-2 mt-2"},"线上缴费更便捷")],-1)),Ct={class:"flex items-center",style:{"margin-left":"60px"}},Tt=["src"],zt={style:{"margin-left":"10px"}},Dt={class:"font-bold text-[13px] ml-2"},Et={class:"text-[11px] text-gray-500 ml-2 mt-2"},jt=J({__name:"order_detail",setup(f){const _=k(null);fe().then(p=>{_.value=p.data});const T=K();Q();const M=T.meta.title,x=parseInt(T.query.order_id),v=k(!0),z=k(null),a=k(null),q=U(()=>new Date().toLocaleDateString()),D=async(p=0)=>{v.value=!0,a.value=null,await he(p).then(({data:l})=>{a.value=l}).catch(()=>{}),v.value=!1};x?D(x):v.value=!1;const P=()=>{oe.confirm(s("orderCloseTips"),s("warning"),{confirmButtonText:s("confirm"),cancelButtonText:s("cancel"),type:"warning"}).then(()=>{be(x).then(()=>{D(x)})})},V=()=>{history.back()},W=()=>{if(!a.value)return;const p=z.value;if(!p)return;const l=document.createElement("iframe");l.style.position="fixed",l.style.right="-9999px",l.style.bottom="-9999px",l.style.width="0",l.style.height="0",l.style.border="none",l.style.visibility="hidden",document.body.appendChild(l),l.src="about:blank",l.onload=()=>{var r;try{const c=l.contentDocument||((r=l.contentWindow)==null?void 0:r.document);if(!c){document.body.removeChild(l);return}c.open(),c.write(`
				<!DOCTYPE html>
				<html>
					<head>
						<title>打印</title>
						<style>
							@page {
								size: 241mm 140mm;
								margin: 0 !important;
							}
							
							body {
								margin: 0 !important;
								padding: 0 !important;
								width: 241mm;
								height: 140mm;
								background-color: white;
								-webkit-print-color-adjust: exact !important;
								color-adjust: exact !important;
								print-color-adjust: exact !important;
							}
							
							.print-content {
								display: block !important;
								width: 241mm;
								height: 140mm;
								padding: 10mm;
								box-sizing: border-box;
								background-color: white;
								position: relative;
							}
							
							.print-section {
								height: 120mm;
								width: 221mm;
								position: relative;
							}
							
							.print-header {
								margin-bottom: 5mm;
							}
							
							.print-header h2 {
								margin: 0 0 3mm 0;
								font-size: 14px;
								font-weight: bold;
								text-align: center;
							}
							
							.print-info {
								font-size: 12px;
								margin-bottom: 5mm;
							}
							
							.flex {
								display: flex;
							}
							
							.justify-between {
								justify-content: space-between;
							}
							
							.print-detail {
								margin: 5mm 0;
							}
							
							table {
								width: 100%;
								border-collapse: collapse;
								font-size: 11px;
								margin: 4mm 0;
							}
							
							table, th, td {
								border: 1px solid #000;
							}
							
							th, td {
								padding: 2mm;
								font-size: 11px;
								text-align: center;
							}
							
							.text-right {
								text-align: right;
							}
							
							.mt-2 {
								margin-top: 2mm;
							}
							
							.font-bold {
								font-weight: bold;
							}
							
							.text-center {
								text-align: center;
							}
							
							.print-footer {
								margin-top: 8mm;
								font-size: 12px;
								position: absolute;
								bottom: 5mm;
								width: calc(100% - 10mm);
								text-align: center;
							}
							
							.border {
								border: 1px solid black !important;
							}
							
							.border-gray-300 {
								border-color: black !important;
							}
							
							.p-1 {
								padding: 2mm;
							}
							
							.w-full {
								width: 100%;
							}
							
							.border-collapse {
								border-collapse: collapse;
							}
						</style>
					</head>
					<body>
						${p.innerHTML}
					</body>
				</html>
			`),c.close(),setTimeout(()=>{var y,g;(y=l.contentWindow)==null||y.focus(),(g=l.contentWindow)==null||g.print(),setTimeout(()=>{document.body.removeChild(l)},2e3)},500)}catch(c){console.error("打印错误:",c),document.body.removeChild(l)}}};return(p,l)=>{var E,j,I,N,B,S,O,F;const r=se,c=ae,y=le,g=ne,w=ie,H=re,$=de,G=ce,Y=_e;return d(),m("div",xe,[e("div",ve,[e("div",{class:"left",onClick:l[0]||(l[0]=h=>V())},[ye,e("span",ge,t(o(s)("returnToPreviousPage")),1)]),we,e("span",ke,t(o(M)),1)]),R((d(),b(G,{model:a.value,"label-width":"100px",ref:"formRef",class:"page-form","label-position":"left"},{default:n(()=>[a.value?(d(),b($,{key:0,class:"box-card !border-none relative",shadow:"never"},{default:n(()=>[e("h3",Ce,t(o(s)("orderInfo")),1),i(y,{class:"row-bg px-[30px] mb-[20px]",gutter:20},{default:n(()=>[i(c,{span:8},{default:n(()=>[i(r,{label:o(s)("orderNo")},{default:n(()=>[e("div",Te,t(a.value.order_no),1)]),_:1},8,["label"]),i(r,{label:o(s)("orderForm")},{default:n(()=>[e("div",ze,t(a.value.order_from_name),1)]),_:1},8,["label"]),a.value.out_trade_no?(d(),b(r,{key:0,label:o(s)("outTradeNo")},{default:n(()=>[e("div",De,t(a.value.out_trade_no),1)]),_:1},8,["label"])):u("",!0),a.value.pay&&a.value.is_local!=1?(d(),b(r,{key:1,label:o(s)("payType")},{default:n(()=>[e("div",Ee,t(a.value.pay.type_name),1)]),_:1},8,["label"])):u("",!0),a.value.is_local==1?(d(),b(r,{key:2,label:o(s)("payType")},{default:n(()=>[e("div",je,t(a.value.local_pay_name),1)]),_:1},8,["label"])):u("",!0)]),_:1}),i(c,{span:8},{default:n(()=>[i(r,{label:o(s)("houseType")},{default:n(()=>[e("div",Ie,t(a.value.house_type_name),1)]),_:1},8,["label"]),i(r,{label:o(s)("buildName")},{default:n(()=>[e("div",Ne,t(a.value.build_name),1)]),_:1},8,["label"]),i(r,{label:o(s)("realname")},{default:n(()=>[e("div",Be,t(a.value.realname),1)]),_:1},8,["label"]),i(r,{label:o(s)("mobile")},{default:n(()=>[e("div",Se,t(a.value.mobile),1)]),_:1},8,["label"])]),_:1})]),_:1}),e("h3",Oe,t(o(s)("orderStatus")),1),e("div",Fe,[e("p",null,[e("span",Re,t(o(s)("orderStatus"))+"：",1),e("span",Le,t(a.value.status_name.name),1)]),e("div",Me,[a.value.status==1?(d(),m("span",{key:0,class:"text-[14px] px-[15px] py-[5px] ml-[30px] text-[#5c96fc] bg-[#ebf3ff] cursor-pointer",onClick:P},t(o(s)("closeOrder")),1)):u("",!0),i(g,{type:"primary",onClick:W},{default:n(()=>[X("打印")]),_:1})])]),e("h3",qe,t(o(s)("billDetail")),1),i(H,{data:a.value.order_item,size:"large"},{default:n(()=>[i(w,{prop:"feeitem",label:o(s)("feeitem"),"min-width":"50",align:"left"},null,8,["label"]),i(w,{prop:"startdate",label:o(s)("startdate"),"min-width":"50",align:"right"},null,8,["label"]),i(w,{prop:"enddate",label:o(s)("enddate"),"min-width":"50",align:"right"},null,8,["label"]),i(w,{prop:"money",label:o(s)("money"),"min-width":"50",align:"right"},null,8,["label"])]),_:1},8,["data"]),e("div",Pe,[e("div",Ve,[e("div",We,[e("div",He,t(o(s)("orderItemMoney")),1),e("div",$e,t(a.value.order_item_money),1)]),e("div",Ge,[e("div",Ye,t(o(s)("orderDiscountMoney")),1),e("div",Ae,t(a.value.order_discount_money),1)]),e("div",Je,[e("div",Ke,t(o(s)("orderMoney")),1),e("div",Qe,t(a.value.order_money),1)])])])]),_:1})):u("",!0)]),_:1},8,["model"])),[[Y,v.value]]),_.value?R((d(),m("div",{key:0,class:"print-content",ref_key:"printContentRef",ref:z},[e("div",Ue,[e("div",Xe,[e("h2",Ze,t(_.value.title),1),e("div",et,[e("div",tt,[e("span",ot,t(o(s)("buildName"))+":"+t((E=a.value)==null?void 0:E.build_name),1)]),e("div",st,[e("span",null,t(o(s)("orderNo"))+":"+t((j=a.value)==null?void 0:j.order_no),1),e("span",null,"打印时间:"+t(o(q)),1)]),e("div",at,[((I=a.value)==null?void 0:I.is_local)!=1?(d(),m("span",lt,t(o(s)("payType"))+":"+t((B=(N=a.value)==null?void 0:N.pay)==null?void 0:B.type_name),1)):(d(),m("span",nt,t(o(s)("payType"))+":"+t((S=a.value)==null?void 0:S.local_pay_name),1))])])]),e("div",it,[e("table",rt,[e("thead",null,[e("tr",null,[e("th",dt,t(o(s)("feeitem")),1),e("th",ct,t(o(s)("startdate")),1),e("th",_t,t(o(s)("enddate")),1),e("th",pt,t(o(s)("money")),1)])]),e("tbody",null,[(d(!0),m(ee,null,te((O=a.value)==null?void 0:O.order_item,(h,A)=>(d(),m("tr",{key:A},[e("td",mt,t(h.feeitem),1),e("td",ut,t(h.startdate),1),e("td",ht,t(h.enddate),1),e("td",bt,t(h.money),1)]))),128))])]),e("div",ft,[e("div",xt,"总金额: "+t((F=a.value)==null?void 0:F.order_item_money),1)])]),e("div",vt,[e("div",yt,[e("div",gt,[e("div",null,[e("img",{src:o(L)(_.value.qrcode),alt:"缴费二维码",style:{width:"60px",height:"60px"}},null,8,wt)]),kt]),e("div",Ct,[e("div",null,[e("img",{src:o(L)(_.value.icon),alt:"LOGO",style:{width:"60px",height:"60px"}},null,8,Tt)]),e("div",zt,[e("div",Dt,t(_.value.name),1),e("div",Et,t(_.value.desc),1)])])])])])],512)),[[Z,!1]]):u("",!0)])}}});const At=ue(jt,[["__scopeId","data-v-d850be14"]]);export{At as default};

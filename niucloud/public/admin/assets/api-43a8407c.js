import{d as j,r as k,b as R,c as C,e as E,g as t,w as s,f as e,z as h,h as d,ee as m,t as r,dQ as P,E as b,az as H,bd as U,a6 as S,p as L,aO as z,aP as O,af as q,s as B,v as w,_ as A}from"./index-4c6d44e8.js";/* empty css                *//* empty css                    *//* empty css                  *//* empty css                 *//* empty css               */const a=y=>(B("data-v-1d9fb858"),y=y(),w(),y),M={class:"p-6"},I={class:"flex items-center justify-between"},K=a(()=>e("span",{class:"text-lg font-semibold"},"API 简易对接文档",-1)),N={class:"space-y-4"},V=a(()=>e("span",{class:"text-sm font-medium"},"鉴权说明",-1)),Q=a(()=>e("div",{class:"text-sm"},[h(" 所有请求需在请求头中携带 "),e("code",null,"Authorization"),h(" 字段，格式为："),e("code",null,"Bearer {API_KEY}")],-1)),Y={class:"api-card"},W={class:"api-header"},D={class:"flex items-center gap-3"},F=a(()=>e("span",{class:"text-base font-semibold"},"获取站点配置消耗",-1)),G=a(()=>e("span",{class:"ml-1"},"复制",-1)),J={class:"api-body"},X=a(()=>e("div",{class:"mb-3"},[e("span",{class:"text-sm font-medium text-gray-600"},"请求地址："),e("code",{class:"text-sm font-mono text-blue-600"},"/open/v1/getmethod")],-1)),Z=a(()=>e("div",{class:"mb-3"},[e("span",{class:"text-sm font-medium text-gray-600"},"请求示例：")],-1)),ee={class:"code-block"},te={class:"text-xs"},se=a(()=>e("div",{class:"mt-4 mb-2"},[e("span",{class:"text-sm font-medium text-gray-600"},"返回结果：")],-1)),ae={class:"code-block"},oe={class:"text-xs"},ne={class:"api-card"},ie={class:"api-header"},ce={class:"flex items-center gap-3"},re=a(()=>e("span",{class:"text-base font-semibold"},"创建任务",-1)),le={class:"api-body"},pe=a(()=>e("div",{class:"mb-3"},[e("span",{class:"text-sm font-medium text-gray-600"},"请求地址："),e("code",{class:"text-sm font-mono text-blue-600"},"/open/v1/createtask")],-1)),de={class:"mb-3 flex items-center justify-between"},_e=a(()=>e("span",{class:"text-sm font-medium text-gray-600"},"请求参数：",-1)),ue=a(()=>e("span",{class:"ml-1"},"复制",-1)),me={class:"code-block"},he={class:"text-xs"},ye=a(()=>e("div",{class:"mt-4 mb-2"},[e("span",{class:"text-sm font-medium text-gray-600"},"返回结果：")],-1)),fe={class:"code-block"},be={class:"text-xs"},xe={class:"api-card"},ve={class:"api-header"},ge={class:"flex items-center gap-3"},ke=a(()=>e("span",{class:"text-base font-semibold"},"查询任务",-1)),Te={class:"api-body"},$e=a(()=>e("div",{class:"mb-3"},[e("span",{class:"text-sm font-medium text-gray-600"},"请求地址："),e("code",{class:"text-sm font-mono text-blue-600"},"/open/v1/querytask")],-1)),je={class:"mb-3 flex items-center justify-between"},Re=a(()=>e("span",{class:"text-sm font-medium text-gray-600"},"请求参数：",-1)),Ce=a(()=>e("span",{class:"ml-1"},"复制",-1)),Ee={class:"code-block"},Pe={class:"text-xs"},He=a(()=>e("div",{class:"mt-4 mb-2"},[e("span",{class:"text-sm font-medium text-gray-600"},"返回结果：")],-1)),Ue={class:"code-block"},Se={class:"text-xs"},Le={class:"api-card bg-gradient-to-br from-purple-50 to-blue-50"},ze={class:"api-header border-purple-200"},Oe={class:"flex items-center gap-3"},qe=a(()=>e("span",{class:"text-base font-semibold text-purple-900"},"完整调用示例",-1)),Be={class:"api-body"},we={class:"flex justify-end mb-2"},Ae=a(()=>e("span",{class:"ml-1"},"复制",-1)),Me={class:"code-block bg-white"},Ie={class:"text-xs"},Ke={class:"flex justify-end mb-2"},Ne=a(()=>e("span",{class:"ml-1"},"复制",-1)),Ve={class:"code-block bg-white"},Qe={class:"text-xs"},Ye={class:"flex justify-end mb-2"},We=a(()=>e("span",{class:"ml-1"},"复制",-1)),De={class:"code-block bg-white"},Fe={class:"text-xs"},Ge={class:"flex justify-end mb-2"},Je=a(()=>e("span",{class:"ml-1"},"复制",-1)),Xe={class:"code-block bg-white"},Ze={class:"text-xs"},et=j({__name:"api",setup(y){const x=k("getmethod"),v=k("javascript"),n=R({getMethod:`curl -X GET "https://your-domain.com/open/v1/getmethod" \\
  -H "Authorization: Bearer sk-lr2E1jjuraHWbHQbpjH9xSCBej7LYM32"`,createTask:`
  {
  "req_key": "duomi_sora_2_temporary_to_video_sora",
  "data": {
     "prompt": "一群人在海边钓鱼",
     "image": [
     "https://xxx.png"
        ], //图像数组，部分模型只支持单图
     "aspect_ratio": "16:9" //比例16:9或者9:16
          }
   }
  `,queryTask:`{
  "req_key": "duomi_sora_2_temporary_to_video_sora",
  "data": {
    "task_id": 245454666545454
  }
}`,getMethodResponse:`{
  "msg": "获取成功",
  "data": [
       {
        "key": "openai_openai_ai_script",
        "name": "AI脚本生成",
        "point": 10,
        "type": "to_image", //方法类型to_image生图，to_video生视频
      }
],
  "res_type": "success"
}`,createTaskResponse:`{
  "msg": "任务创建成功",
  "data": {
    "image_url": "https://xx.png"
  },//此处内容根据type类型不同而不同
  "res_type": "success",//success任务创建成功/完成  fail任务失败  ing任务进行中
  "task_id": "cogview_20260205658659112009728"
}`,queryTaskResponse:`{
  "msg": "查询成功",
  "data": {
    "image_url": "https://xx.png", //生图成功返回
    "video_url": "https://xx.mp4" //生视频成功返回
  },
  "res_type": "success"
}`,javascript:`// JavaScript 调用示例
const apiKey = 'sk-lr2E1jjuraHWbHQbpjH9xSCBej7LYM32';
const baseUrl = 'https://your-domain.com';

// 1. 获取站点配置消耗
async function getMethod() {
  const response = await fetch(\`\${baseUrl}/open/v1/getmethod\`, {
    method: 'GET',
    headers: {
      'Authorization': \`Bearer \${apiKey}\`
    }
  });
  return await response.json();
}

// 2. 创建任务
async function createTask(prompt, images, aspectRatio = '16:9') {
  const response = await fetch(\`\${baseUrl}/open/v1/createtask\`, {
    method: 'POST',
    headers: {
      'Authorization': \`Bearer \${apiKey}\`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      prompt: prompt,
      image: images,
      aspect_ratio: aspectRatio
    })
  });
  return await response.json();
}

// 3. 查询任务
async function queryTask(taskId) {
  const response = await fetch(\`\${baseUrl}/open/v1/querytask\`, {
    method: 'POST',
    headers: {
      'Authorization': \`Bearer \${apiKey}\`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      req_key: 'openai_openai_ai_script',
      data: {
        task_id: taskId
      }
    })
  });
  return await response.json();
}`,php:`<?php
// PHP 调用示例
$apiKey = 'sk-lr2E1jjuraHWbHQbpjH9xSCBej7LYM32';
$baseUrl = 'https://your-domain.com';

// 1. 获取站点配置消耗
function getMethod($baseUrl, $apiKey) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . '/open/v1/getmethod');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $apiKey
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

// 2. 创建任务
function createTask($baseUrl, $apiKey, $prompt, $images, $aspectRatio = '16:9') {
    $data = [
        'prompt' => $prompt,
        'image' => $images,
        'aspect_ratio' => $aspectRatio
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . '/open/v1/createtask');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

// 3. 查询任务
function queryTask($baseUrl, $apiKey, $taskId) {
    $data = [
        'req_key' => 'openai_openai_ai_script',
        'data' => [
            'task_id' => $taskId
        ]
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . '/open/v1/querytask');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}
?>`,python:`# Python 调用示例
import requests
import json

api_key = 'sk-lr2E1jjuraHWbHQbpjH9xSCBej7LYM32'
base_url = 'https://your-domain.com'

# 1. 获取站点配置消耗
def get_method():
    headers = {
        'Authorization': f'Bearer {api_key}'
    }
    response = requests.get(f'{base_url}/open/v1/getmethod', headers=headers)
    return response.json()

# 2. 创建任务
def create_task(prompt, images, aspect_ratio='16:9'):
    headers = {
        'Authorization': f'Bearer {api_key}',
        'Content-Type': 'application/json'
    }
    data = {
        'prompt': prompt,
        'image': images,
        'aspect_ratio': aspect_ratio
    }
    response = requests.post(
        f'{base_url}/open/v1/createtask',
        headers=headers,
        json=data
    )
    return response.json()

# 3. 查询任务
def query_task(task_id):
    headers = {
        'Authorization': f'Bearer {api_key}',
        'Content-Type': 'application/json'
    }
    data = {
        'req_key': 'openai_openai_ai_script',
        'data': {
            'task_id': task_id
        }
    }
    response = requests.post(
        f'{base_url}/open/v1/querytask',
        headers=headers,
        json=data
    )
    return response.json()

# 使用示例
if __name__ == '__main__':
    # 获取配置
    config = get_method()
    print(config)
    
    # 创建任务
    task = create_task(
        '一群人在海边钓鱼',
        ['https://xxx.png'],
        '16:9'
    )
    print(task)
    
    # 查询任务
    result = query_task(245454666545454)
    print(result)`,curl:`# cURL 调用示例

# 1. 获取站点配置消耗
curl -X GET "https://your-domain.com/open/v1/getmethod" \\
  -H "Authorization: Bearer sk-lr2E1jjuraHWbHQbpjH9xSCBej7LYM32"

# 2. 创建任务
curl -X POST "https://your-domain.com/open/v1/createtask" \\
  -H "Authorization: Bearer sk-lr2E1jjuraHWbHQbpjH9xSCBej7LYM32" \\
  -H "Content-Type: application/json" \\
  -d '{
    "prompt": "一群人在海边钓鱼",
    "image": ["https://xxx.png"],
    "aspect_ratio": "16:9"
  }'

# 3. 查询任务
curl -X POST "https://your-domain.com/open/v1/querytask" \\
  -H "Authorization: Bearer sk-lr2E1jjuraHWbHQbpjH9xSCBej7LYM32" \\
  -H "Content-Type: application/json" \\
  -d '{
    "req_key": "openai_openai_ai_script",
    "data": {
      "task_id": 245454666545454
    }
  }'`}),_=async f=>{try{await navigator.clipboard.writeText(f),b.success("复制成功")}catch{try{const i=document.createElement("textarea");i.value=f,i.style.position="fixed",i.style.opacity="0",document.body.appendChild(i),i.select(),document.execCommand("copy"),document.body.removeChild(i),b.success("复制成功")}catch{b.error("复制失败，请手动复制")}}};return(f,o)=>{const i=H,T=U,l=S,u=L,p=z,g=O,$=q;return C(),E("div",M,[t($,{class:"box-card !border-none",shadow:"never"},{header:s(()=>[e("div",I,[K,t(i,{type:"info"},{default:s(()=>[h("聚合请求方式")]),_:1})])]),default:s(()=>[e("div",N,[t(T,{type:"warning",closable:!1,"show-icon":""},{title:s(()=>[V]),default:s(()=>[Q]),_:1}),t(g,{modelValue:x.value,"onUpdate:modelValue":o[8]||(o[8]=c=>x.value=c),type:"card",class:"api-tabs"},{default:s(()=>[t(p,{label:"获取消耗",name:"getmethod"},{default:s(()=>[e("div",Y,[e("div",W,[e("div",D,[t(i,{type:"success",size:"small"},{default:s(()=>[h("GET")]),_:1}),F]),t(u,{type:"primary",size:"small",plain:"",onClick:o[0]||(o[0]=c=>_(n.getMethod))},{default:s(()=>[t(l,null,{default:s(()=>[t(d(m))]),_:1}),G]),_:1})]),e("div",J,[X,Z,e("div",ee,[e("pre",te,r(n.getMethod),1)]),se,e("div",ae,[e("pre",oe,r(n.getMethodResponse),1)])])])]),_:1}),t(p,{label:"创建任务",name:"createtask"},{default:s(()=>[e("div",ne,[e("div",ie,[e("div",ce,[t(i,{type:"warning",size:"small"},{default:s(()=>[h("POST")]),_:1}),re])]),e("div",le,[pe,e("div",de,[_e,t(u,{type:"primary",size:"small",plain:"",onClick:o[1]||(o[1]=c=>_(n.createTask))},{default:s(()=>[t(l,null,{default:s(()=>[t(d(m))]),_:1}),ue]),_:1})]),e("div",me,[e("pre",he,r(n.createTask),1)]),ye,e("div",fe,[e("pre",be,r(n.createTaskResponse),1)])])])]),_:1}),t(p,{label:"查询任务",name:"querytask"},{default:s(()=>[e("div",xe,[e("div",ve,[e("div",ge,[t(i,{type:"warning",size:"small"},{default:s(()=>[h("POST")]),_:1}),ke])]),e("div",Te,[$e,e("div",je,[Re,t(u,{type:"primary",size:"small",plain:"",onClick:o[2]||(o[2]=c=>_(n.queryTask))},{default:s(()=>[t(l,null,{default:s(()=>[t(d(m))]),_:1}),Ce]),_:1})]),e("div",Ee,[e("pre",Pe,r(n.queryTask),1)]),He,e("div",Ue,[e("pre",Se,r(n.queryTaskResponse),1)])])])]),_:1}),t(p,{label:"调用实例",name:"examples"},{default:s(()=>[e("div",Le,[e("div",ze,[e("div",Oe,[t(l,{class:"text-purple-600 text-xl"},{default:s(()=>[t(d(P))]),_:1}),qe])]),e("div",Be,[t(g,{modelValue:v.value,"onUpdate:modelValue":o[7]||(o[7]=c=>v.value=c),type:"card",class:"language-tabs"},{default:s(()=>[t(p,{label:"JavaScript",name:"javascript"},{default:s(()=>[e("div",we,[t(u,{type:"primary",size:"small",plain:"",onClick:o[3]||(o[3]=c=>_(n.javascript))},{default:s(()=>[t(l,null,{default:s(()=>[t(d(m))]),_:1}),Ae]),_:1})]),e("div",Me,[e("pre",Ie,r(n.javascript),1)])]),_:1}),t(p,{label:"PHP",name:"php"},{default:s(()=>[e("div",Ke,[t(u,{type:"primary",size:"small",plain:"",onClick:o[4]||(o[4]=c=>_(n.php))},{default:s(()=>[t(l,null,{default:s(()=>[t(d(m))]),_:1}),Ne]),_:1})]),e("div",Ve,[e("pre",Qe,r(n.php),1)])]),_:1}),t(p,{label:"Python",name:"python"},{default:s(()=>[e("div",Ye,[t(u,{type:"primary",size:"small",plain:"",onClick:o[5]||(o[5]=c=>_(n.python))},{default:s(()=>[t(l,null,{default:s(()=>[t(d(m))]),_:1}),We]),_:1})]),e("div",De,[e("pre",Fe,r(n.python),1)])]),_:1}),t(p,{label:"cURL",name:"curl"},{default:s(()=>[e("div",Ge,[t(u,{type:"primary",size:"small",plain:"",onClick:o[6]||(o[6]=c=>_(n.curl))},{default:s(()=>[t(l,null,{default:s(()=>[t(d(m))]),_:1}),Je]),_:1})]),e("div",Xe,[e("pre",Ze,r(n.curl),1)])]),_:1})]),_:1},8,["modelValue"])])])]),_:1})]),_:1},8,["modelValue"])])]),_:1})])}}});const ct=A(et,[["__scopeId","data-v-1d9fb858"]]);export{ct as default};

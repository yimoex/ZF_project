# ZF_project
某教务系统的通用框架 (可扩展)



## 法律提示
- 本代码未经平台官方授权，爬取行为可能违反其服务条款或法律法规。

- 使用者需自行承担所有风险，禁止用于非法用途。

- 作者不对数据的滥用、版权纠纷或法律后果负责。

  

## 食用方法

> 1. 修改API地址为你的目标地址：

   ```php
   class Zf {
   
       const API = 'https://'; //这里改成你的目标地址!
   
       private $http;
   //--snip--
   ```

> 2. 初始化类

   ```php
   $zf = new Zf('学号');
   $app = new App(); //初始化能够显示表格的类
   $rec = $zf -> login('密码');
   $res = $zf -> getExam(); //拉取数据
   $app -> showTab($res); //显示表格
   ```

> 3. 扩展功能

在 `yimoEx/ai/models` 路径下存放了模型的基础文件，可以添加自己的模型用于 `OCR识别`

##### 注：模型必须有OCR能力才能使用！



#### 框架逻辑

1. 登录 (以模型的OCR能力进行识别，进行处理后返回)
2. 验证当前 `Session`，以验证登录凭证
3. 可处理主页中的内容!



### 已支持的功能

- [x] 获取考试成绩 (`getExamGrade`)
- [x] 获取考试信息 (`getExam`)
- [x] 自动OCR验证码 (`resolveCaptach`)
- [x] 手动识别验证码 (`resolveCaptachHuman`)


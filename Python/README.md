# Python sample codes
## ControlWebsite.py
자동화작업을 위해 개발한 웹컨트롤 클래스. (selenium을 사용)

아래와 같이 간편하게 웹을 컨트롤 할수 있다.
```python
instance = ControlWebsite();
flow_list = []
flow_list.append({'goto_page':'https://test.com/loginPage'}) # 로그인페이지 접속
flow_list.append({'send_key':{'아이디':'//*[@id="id"]'}}) # 아이디 입력
flow_list.append({'send_key':{'비밀번호`':'//*[@id="password"]'}}) # 비밀번호 입력
flow_list.append({'click':'//*[@id="wrapper"]/div/form/div[2]/input'}) # 로그인버튼 클릭
for row in flow_list:
    instance.start_flow(row)
```

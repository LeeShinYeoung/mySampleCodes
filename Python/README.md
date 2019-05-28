# Python sample codes
## ControlWebsite.py
자동화작업을 위해 개발한 웹컨트롤 클래스 (selenium을 사용)

아래와 같이 간편하게 웹을 컨트롤 할수 있습니다.

### 실행
```python
# 인스턴스 생성
inst = ControlWebsite()
```
#### base_controller()를 사용할 경우
```python
inst.base_controller('goto_page', 'https://test.com/loginPage')
inst.base_controller('send_key', {'아이디': '//*[@id="id"]'})
inst.base_controller('send_key', {'비밀번호`': '//*[@id="password"]'})
inst.base_controller('click', '//*[@id="wrapper"]/div/form/div[2]/input')
```
#### exec_row()를 사용할 경우
```python
inst.exec_row({'goto_page': 'https://test.com/loginPage'})
inst.exec_row({'send_key': {'아이디': '//*[@id="id"]'}})
inst.exec_row({'send_key': {'비밀번호`': '//*[@id="password"]'}})
inst.exec_row({'click': '//*[@id="wrapper"]/div/form/div[2]/input'})
```
#### exec_flow_list()를 사용할 경우
```python
flow = [
    {'goto_page': 'https://test.com/loginPage'}
    , {'send_key': {'아이디': '//*[@id="id"]'}}
    , {'send_key': {'비밀번호`': '//*[@id="password"]'}}
    , {'click': '//*[@id="wrapper"]/div/form/div[2]/input'}
]
inst.exec_flow_list(flow)
```

#### Parameter
| event_type   | param                                 | explain              |
| ------------ | ------------------------------------- | -------------------- |
| goto_page    | string : Url                          | 페이지 접속          |
| click        | string : Xpath                        | 클릭                 |
| select       | dict : {Option name(str): Xpath(str)} | Selectbox 선택       |
| send_key     | dict : {Text(str): Xpath(str)}        | 태그에 값 입력       |
| alert        | boolean : True \| False               | Alert 확인 혹은 취소 |
| switch_frame | string : Xpath                        | Iframe 포커스 이동   |
| clear_text   | string : Xpath                        | 선택태그 값 초기화   |
| sleep        | int : second                          | 실행 딜레이          |

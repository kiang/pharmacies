# 藥局口罩採購地圖

![藥局口罩採購地圖](https://kiang.github.io/pharmacies/og_image.png)

順應台灣在 2020/02/06 實施口罩購買實名制度，藥局口罩採購地圖試著合併既有開放資料與健保署提供的最新庫存資料後在地圖呈現，目前使用到的資料如下：

1. [健保特約醫事機構-藥局](https://data.gov.tw/dataset/39284)
2. [健保特約醫事機構-診所](https://data.gov.tw/dataset/39283) - 衛生所部份
3. [全民健康保險特約院所固定服務時段](https://data.gov.tw/dataset/39291)
4. [健保特約機構口罩剩餘數量明細清單](https://data.gov.tw/dataset/116285)

以上述資料為基礎，搭配 [TGOS 全國門牌地址定位服務](https://www.tgos.tw/tgos/Web/Address/TGOS_Address.aspx) 取得對應的地理座標、縣市、鄉鎮市區與村里等欄位

後續因為藥局回饋希望在資料加入自訂備註，因此設計了一個 [Google表單](https://forms.gle/7jFfScLedN3A8ENA8) 蒐集資料，內容會不定期更新到 raw/custom_notices.csv

部份資料的地理座標使用了 [國土測繪圖資服務雲](https://maps.nlsc.gov.tw/T09/mapshow.action?language=ZH) 進行檢索，同時也併入了來自 [@lanma](https://github.com/WJWang/mask-help-info-api/issues/2) 提供的修正

## 程式說明

系統排程
```
* 07-22 * * * /usr/bin/php -q /home/kiang/github/pharmacies/scripts/cron.php
```

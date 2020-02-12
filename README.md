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

相關應用與說明可以參考 [口罩供需資訊平台](https://g0v.hackmd.io/@kiang/mask-info)

## 介紹文章

1. [藥局口罩採購地圖上線](https://medium.com/%E6%B1%9F%E6%98%8E%E5%AE%97-kiang/%E8%97%A5%E5%B1%80%E5%8F%A3%E7%BD%A9%E6%8E%A1%E8%B3%BC%E5%9C%B0%E5%9C%96%E4%B8%8A%E7%B7%9A-54e11bd63e84)
2. [藥局口罩採購地圖上線後記](https://medium.com/%E6%B1%9F%E6%98%8E%E5%AE%97-kiang/%E8%97%A5%E5%B1%80%E5%8F%A3%E7%BD%A9%E6%8E%A1%E8%B3%BC%E5%9C%B0%E5%9C%96%E4%B8%8A%E7%B7%9A%E5%BE%8C%E8%A8%98-8277f1343816)
3. [藥局口罩採購地圖加入搜尋功能](https://medium.com/%E6%B1%9F%E6%98%8E%E5%AE%97-kiang/%E8%97%A5%E5%B1%80%E5%8F%A3%E7%BD%A9%E6%8E%A1%E8%B3%BC%E5%9C%B0%E5%9C%96%E5%8A%A0%E5%85%A5%E6%90%9C%E5%B0%8B%E5%8A%9F%E8%83%BD-c4876418c612)

## 程式說明

系統排程
```
* 07-22 * * * /usr/bin/php -q /home/kiang/github/pharmacies/scripts/cron.php
```

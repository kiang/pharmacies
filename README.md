[English](#pharmacy-mask-map) | [中文](#藥局口罩採購地圖)

# Pharmacy Mask Map

![Pharmacy Mask Map](https://kiang.github.io/pharmacies/og_image.png)

## Background

During the COVID-19 pandemic in early 2020, Taiwan faced a critical shortage of facial masks. To ensure fair distribution, the government implemented a name-based rationing system on February 6, 2020. The system controlled mask distribution through pharmacies and health centers while providing real-time inventory data.

This project creates an interactive map that helps people:
- Find nearby pharmacies with mask stocks
- Check real-time mask inventory levels
- View pharmacy operating hours and contact information
- Get custom notices from pharmacies
- Navigate to their chosen pharmacy

## Data Sources

The data currently used includes:

1. [NHI Contracted Medical Institutions - Pharmacies](https://data.gov.tw/dataset/39284)
2. [NHI Contracted Medical Institutions - Clinics](https://data.gov.tw/dataset/39283) - Health Centers
3. [Fixed Service Schedule of NHI Contracted Institutions](https://data.gov.tw/dataset/39291)
4. [Mask Stock Details of NHI Contracted Institutions](https://data.gov.tw/dataset/116285)

Based on the above data, combined with [TGOS National Address Positioning Service](https://www.tgos.tw/tgos/Web/Address/TGOS_Address.aspx) to obtain corresponding geographic coordinates, cities, districts, and villages.

Due to pharmacy feedback requesting custom notes in the data, a [Google Form](https://forms.gle/7jFfScLedN3A8ENA8) was designed to collect data, which is periodically updated to raw/custom_notices.csv

Some geographic coordinates were searched using [National Land Surveying and Mapping Center](https://maps.nlsc.gov.tw/T09/mapshow.action?language=ZH), and corrections provided by [@lanma](https://github.com/WJWang/mask-help-info-api/issues/2) were also incorporated.

For related applications and information, please refer to [Mask Supply and Demand Information Platform](https://g0v.hackmd.io/@kiang/mask-info)

## Articles

1. [Pharmacy Mask Map Launch](https://medium.com/%E6%B1%9F%E6%98%8E%E5%AE%97-kiang/%E8%97%A5%E5%B1%80%E5%8F%A3%E7%BD%A9%E6%8E%A1%E8%B3%BC%E5%9C%B0%E5%9C%96%E4%B8%8A%E7%B7%9A-54e11bd63e84)
2. [Pharmacy Mask Map Post-Launch Notes](https://medium.com/%E6%B1%9F%E6%98%8E%E5%AE%97-kiang/%E8%97%A5%E5%B1%80%E5%8F%A3%E7%BD%A9%E6%8E%A1%E8%B3%BC%E5%9C%B0%E5%9C%96%E4%B8%8A%E7%B7%9A%E5%BE%8C%E8%A8%98-8277f1343816)
3. [Search Function Added to Pharmacy Mask Map](https://medium.com/%E6%B1%9F%E6%98%8E%E5%AE%97-kiang/%E8%97%A5%E5%B1%80%E5%8F%A3%E7%BD%A9%E6%8E%A1%E8%B3%BC%E5%9C%B0%E5%9C%96%E5%8A%A0%E5%85%A5%E6%90%9C%E5%B0%8B%E5%8A%9F%E8%83%BD-c4876418c612)

## System Configuration

Cron schedule
```
* 07-22 * * * /usr/bin/php -q /home/kiang/github/pharmacies/scripts/cron.php
```

---

# 藥局口罩採購地圖

![藥局口罩採購地圖](https://kiang.github.io/pharmacies/og_image.png)

## 背景說明

因應 2020 年初 COVID-19 疫情期間口罩短缺，台灣政府於 2020 年 2 月 6 日實施口罩實名制。為協助民眾即時查詢哪裡有口罩可以購買，本計畫整合了政府提供的開放資料，建置了這個互動式地圖，提供以下功能：
- 快速找出附近有口罩存貨的藥局
- 即時查看口罩庫存數量
- 顯示藥局營業時間與聯絡資訊
- 提供藥局的重要提醒訊息
- 導航至所選藥局位置

目前使用到的資料如下：

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

<?php
return ['pages' => <<<'PAGES'
// PAGE_BEGIN
{
    "root": "addon/baijia_tools",
    "pages": [
        {
            "path": "pages/home/index",
            "style": {
                "navigationStyle": "custom",
                "navigationBarTitleText": "的确省"
            }
        },
        {
            "path": "pages/map/index",
            "style": {
                "navigationStyle": "custom",
                "navigationBarTitleText": "生活街区"
            }
        },
        {
            "path": "pages/mine/index",
            "style": {
                "navigationStyle": "custom",
                "navigationBarTitleText": "我的"
            }
        },
        {
            "path": "pages/service/index",
            "style": {
                "navigationBarTitleText": "服务"
            }
        },
        {
            "path": "pages/promotion/index",
            "style": { "navigationBarTitleText": "推广中心" }
        },
        {"path": "pages/movie/seats", "style": {"navigationBarTitleText": "选择座位"}},
        {"path": "pages/movie/ticket", "style": {"navigationBarTitleText": "我的影票"}},
        {
            "path": "pages/movie/index",
            "style": { "navigationBarTitleText": "热门电影" }
        },
        {
            "path": "pages/dining/index",
            "style": { "navigationBarTitleText": "选门店" }
        },
        {
            "path": "pages/dining/menu",
            "style": { "navigationBarTitleText": "到店自取" }
        },
        {
            "path": "pages/mayi/index",
            "style": { "navigationBarTitleText": "蚂蚁星球" }
        },
        {
            "path": "pages/catalog/index",
            "style": { "navigationBarTitleText": "美团到店" }
        },
        {
            "path": "pages/orders/index",
            "style": {
                "navigationBarTitleText": "我的订单"
            }
        }
    ]
},
// PAGE_END
PAGES
];

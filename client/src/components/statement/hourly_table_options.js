/**
 * Created by liudian on 9/12/17.
 */

let options = {
    vuetableUrl: '/admin/api/statement/hourly',

    tableFields: [
        {
            name: 'date',
            title: '日期',
        },
        {
            name: 'card_total',
            title: '房卡购买量',
        },
        {
            name: 'coin_total',
            title: '金币购买量'
        },
    ],

    sortOrder: [    //默认的排序
        {
            field: 'id',
            sortField: 'id',
            direction: 'desc',
        }
    ],
};

export default options;
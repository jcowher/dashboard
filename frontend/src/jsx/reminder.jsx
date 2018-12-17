'use strict';

import React from 'react';
import numeral from 'numeral';

export default class Reminder extends React.Component {

    render() {
        return (
            <tr className="reminder">
                <th scope="row" align="left">{this.props.data.label}</th>
                <td align="left" width="200">{this.props.data.start_date}</td>
                <td align="left" width="200">{numeral(this.props.data.amount).format('$0,0.00')}</td>
            </tr>
        );
    }
}
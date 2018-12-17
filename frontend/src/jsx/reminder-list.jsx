import React, {Component} from 'react';
import PropTypes from 'prop-types';
import {withStyles} from "@material-ui/core/styles";
import {
    Checkbox,
    Chip,
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableRow,
    Typography
} from '@material-ui/core';
import numeral from 'numeral';
import {blue} from '@material-ui/core/colors';
import update from 'immutability-helper';
import DialogEditReminder from "./dialog-edit-reminder";

const styles = theme => ({
    cellCheckbox: {
        paddingRight: 0,
        width: 20
    },
    checkbox: {
        paddingRight: 0
    },
    iconClose: {
        position: 'absolute',
        right: 0,
        top: 0,
        color: theme.palette.grey[500]
    },
    iconRight: {
        marginLeft: theme.spacing.unit
    },
    table: {
        width: '100%',
        marginBottom: 64
    },
    tableRow: {
        cursor: 'pointer',
        '&:hover': {
            backgroundColor: blue[50]
        }
    },
    tableRowStriped: {
        backgroundColor: theme.palette.grey[200]
    }
});

class ReminderList extends Component {

    state = {
        item: null,
        selected: []
    };

    openEditDialog = item => {
        this.setState({item: item});
    };

    closeEditDialog = () => {
        this.setState({item: null});
    };

    toggleAll = (e) => {
        if (e.target.checked) {
            this.setState({
                selected: this.props.items.map((item, index) => {
                    return index.toString();
                })
            });
        } else {
            this.setState({selected: []});
        }
    };

    toggleOne = e => {
        if (e.target.checked) {
            this.setState({
                selected: update(this.state.selected, {$push: [e.target.value]})
            });
        } else {
            let state = Object.assign({}, this.state);
            let index = state.selected.indexOf(e.target.value);
            this.setState(update(this.state, {selected: {$splice: [[index, 1]]}}));
        }
    };

    render() {
        const {classes, items} = this.props;
        const {item, selected} = this.state;

        let dialog;
        if (item !== null) {
            dialog = <DialogEditReminder item={item} onClose={this.closeEditDialog}/>
        }

        return (
            <>
                {dialog}
                <Table className={classes.table}>
                    <TableHead>
                        <TableRow>
                            <TableCell className={classes.cellCheckbox} padding="checkbox">
                                <Checkbox
                                    className={classes.checkbox}
                                    checked={selected.length === items.length}
                                    onChange={this.toggleAll}
                                    value="1"
                                />
                            </TableCell>
                            <TableCell>Label</TableCell>
                            <TableCell>Status</TableCell>
                            <TableCell numeric>Date</TableCell>
                            <TableCell numeric>Amount</TableCell>
                        </TableRow>
                    </TableHead>
                    <TableBody>
                        {items.map((item, index) => {
                            let key = index.toString();
                            let tableRowClass = classes.tableRow;
                            if (index % 2 === 0) {
                                tableRowClass += ` ${classes.tableRowStriped}`
                            }
                            return (
                                <TableRow
                                    key={key}
                                    className={tableRowClass}
                                >
                                    <TableCell className={classes.cellCheckbox} padding="checkbox">
                                        <Checkbox
                                            checked={selected.indexOf(key) >= 0}
                                            onChange={this.toggleOne}
                                            value={key}
                                        />
                                    </TableCell>
                                    <TableCell onClick={this.openEditDialog.bind(this, item)}>
                                        <Typography variant="button">{item.label}</Typography>
                                        <Typography color="textSecondary">{item.description || ''}</Typography>
                                    </TableCell>
                                    <TableCell onClick={this.openEditDialog.bind(this, item)}>
                                        <Chip
                                            label={item.status}
                                            color={item.status === 'past due' ? 'secondary' : 'default'}
                                            variant="outlined"
                                        />
                                    </TableCell>
                                    <TableCell
                                        numeric
                                        onClick={this.openEditDialog.bind(this, item)}
                                    >{item.start_date}</TableCell>
                                    <TableCell
                                        numeric
                                        onClick={this.openEditDialog.bind(this, item)}
                                    >{numeral(item.amount).format('$0,0.00')}</TableCell>
                                </TableRow>
                            )
                        })}
                    </TableBody>
                </Table>
            </>
        )
    }

}

ReminderList.propTypes = {
    classes: PropTypes.object.isRequired,
    items: PropTypes.array.isRequired
};

export default withStyles(styles)(ReminderList);
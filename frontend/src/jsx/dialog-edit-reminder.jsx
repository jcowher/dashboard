import React, {Component} from 'react';
import PropTypes from 'prop-types';
import {withStyles} from '@material-ui/core/styles';
import {Button, Dialog, DialogActions, DialogContent, IconButton, InputAdornment, TextField} from '@material-ui/core';
import CloseIcon from '@material-ui/icons/close';
import numeral from 'numeral';

const styles = theme => ({
    dialogActionLeft: {
        alignSelf: 'flex-start'
    },
    leftInput: {
        marginRight: 10,
        width: 'calc(50% - 10px)'
    },
    rightInput: {
        marginLeft: 10,
        width: 'calc(50% - 10px)'
    }
});

class DialogEditReminder extends Component {

    constructor(props) {
        super(props);
        this.state = {
            startDate: props.item.start_date || null
        };
    };

    changeStartDate = e => {
        this.setState({startDate: e.target.value});
    };

    close = () => {
        this.props.onClose();
    };

    save = () => {
        this.props.onClose();
    };

    deleteException = () => {
        if (confirm('Are you sure you want to delete this exception?')) {
            this.props.onClose();
        }
    };

    render() {
        const {classes, item} = this.props;
        const {startDate} = this.state;

        let btnDeleteException = null;
        if (item.parent && item.id > 0 && item.status !== 'past due') {
            btnDeleteException = <Button className={classes.dialogActionLeft} onClick={this.deleteException} color="secondary">Delete Exception</Button>;
        }

        return (
            <Dialog open={true}>
                <DialogActions>
                    <IconButton onClick={this.close}>
                        <CloseIcon/>
                    </IconButton>
                </DialogActions>
                <DialogContent>
                    <TextField
                        margin="normal"
                        id="label"
                        label="Label"
                        variant="outlined"
                        defaultValue={item.label || ''}
                        fullWidth
                        required
                        autoFocus
                    />
                    <TextField
                        margin="normal"
                        id="start-date"
                        label="Start Date"
                        type="date"
                        variant="outlined"
                        className={classes.leftInput}
                        onChange={this.changeStartDate}
                        defaultValue={item.start_date || ''}
                        InputLabelProps={{shrink: true}}
                        required
                    />
                    <TextField
                        margin="normal"
                        id="end-date"
                        label="End Date"
                        type="date"
                        variant="outlined"
                        className={classes.rightInput}
                        defaultValue={item.parent && item.id === null ? item.parent.end_date : item.end_date}
                        inputProps={{min: startDate}}
                        InputLabelProps={{shrink: true}}
                    />
                    <TextField
                        margin="normal"
                        id="amount"
                        label="Amount"
                        type="number"
                        variant="outlined"
                        defaultValue={numeral(item.amount).format('0.00') || ''}
                        fullWidth
                        required
                        inputProps={{step: 0.01}}
                        InputProps={{startAdornment: <InputAdornment position="start">$</InputAdornment>}}
                    />
                </DialogContent>
                <DialogActions>
                    {btnDeleteException}
                    <Button onClick={this.save} color="primary" variant="contained">Save</Button>
                </DialogActions>
            </Dialog>
        )
    }

}

DialogEditReminder.propTypes = {
    classes: PropTypes.object.isRequired,
    item: PropTypes.object,
    onClose: PropTypes.func.isRequired
};

DialogEditReminder.defaultProps = {
    item: {}
};

export default withStyles(styles)(DialogEditReminder);
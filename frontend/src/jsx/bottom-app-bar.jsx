import React, {Component} from 'react';
import PropTypes from 'prop-types';
import {withStyles} from '@material-ui/core/styles';
import {AppBar, Button, Toolbar} from '@material-ui/core';
import AddIcon from '@material-ui/icons/Add';
import DialogEditReminder from './dialog-edit-reminder.jsx';

const styles = () => ({
    appBar: {
        top: 'auto',
        bottom: 0,
    },
    toolbar: {
        alignItems: 'center',
        justifyContent: 'space-between'
    },
    fabButton: {
        position: 'absolute',
        zIndex: 1,
        top: -30,
        right: 0,
        left: 0,
        margin: '0 auto'
    }
});

class BottomAppBar extends Component {

    state = {
        dialogOpen: false
    };

    closeDialog = () => {
        this.setState({dialogOpen: false});
    };

    openDialog = () => {
        this.setState({dialogOpen: true});
    };

    render() {
        const {classes} = this.props;
        const {dialogOpen} = this.state;

        let dialog;
        if (dialogOpen) {
            dialog = <DialogEditReminder onClose={this.closeDialog}/>;
        }

        return (
            <>
                {dialog}
                <AppBar position="fixed" color="primary" className={classes.appBar}>
                    <Toolbar className={classes.toolbar}>
                        <Button
                            variant="fab"
                            color="secondary"
                            aria-label="Add"
                            className={classes.fabButton}
                            onClick={this.openDialog}
                        >
                            <AddIcon/>
                        </Button>
                    </Toolbar>
                </AppBar>
            </>
        );
    }

}

BottomAppBar.propTypes = {
    classes: PropTypes.object.isRequired
};

export default withStyles(styles)(BottomAppBar);

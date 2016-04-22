import React, { Component } from 'react';
import ReactDOM from 'react-dom';
import axios from 'axios';

import { showSearching, hideSearching } from '../../util/utils';

export default class Standardv extends Component{
  constructor(props) {
    super(props);
    this.state = {
      id: (props.id ? props.id : 0),
      tid: (props.tid ? props.tid : 0),
	  fields: [],
      standard: {},
    };
  }

  render() {
    const standard = this.state.standard;
	const subject = (this.state.standard.subject ? this.state.standard.subject : <div className="center">모범단협 없음</div>);

    let rows = [];
    for (let key in this.state.fields) {
      if (standard[this.state.fields[key].field]) {
	    const cname = this.state.fields[key].field;
        rows.push(<div className={cname} key={key}>
          <h3>{this.state.fields[key].subject}</h3>
          <p dangerouslySetInnerHTML={{ __html: standard[this.state.fields[key].field] }} />
        </div>);
      }
    }

    return (
      <div className="guide-clause-info overlay">
        <div className="guide-clause-container">
          <div className="guide-clause-info-box">
            <div className="whole-document">
              <div className="guide-document">
                <h2>{subject}</h2>
                <p classNname="guide-content" dangerouslySetInnerHTML={{ __html: standard.content }} />
                {rows}
				<div className="guide-box-button">
                  <button onClick={this.onStandardClose.bind(this)}>닫기</button>
                  <button onClick={this.onStandardGo.bind(this)}>모범단협 전체보기</button>
				</div>
              </div>
            </div>
			<i className="close fa fa-close" onClick={this.onStandardClose.bind(this)}></i>
          </div>
        </div>
		<div className="guide-background" onClick={this.onStandardClose.bind(this)}></div>
      </div>
    );
  }

  componentWillMount() {
    this.doSearch();
  }

  componentDidMount() {
    var self = this;
    jQuery(window).bind('resize.standardv',function(e) {
      self.handleResize();
    });
  	this.overlayResize();

    var b = jQuery(ReactDOM.findDOMNode(this)).find('.guide-background');
    if(b.length > 0) {
      jQuery(window).bind('keydown.standardv',function(event) {
        var code = event.charCode || event.keyCode;
        if(code == 27) {
          self.onStandardClose();
        }
      });
    }
  }

  componentDidUpdate() {
    this.overlayResize();
  }

  componentWillUnmount() {
    jQuery(window).unbind('resize.standardv');
    jQuery(window).unbind('keydown.standardv');
  }

  handleResize() {
    this.overlayResize();
  }

  overlayResize() {
    var winWidth = window.innerWidth;
    var winHeight = window.innerHeight;
    var obj = jQuery(ReactDOM.findDOMNode(this)).find('.guide-clause-container');
    var w = Math.min(600, parseInt( winWidth * 0.9 ) );
    var max_h = parseInt( winHeight * 0.9 );
    obj.css({
      'width' : w+'px',
      'max-height' : max_h+'px',
      'left' : parseInt( ( winWidth - w ) / 2 )+'px'
    });
    var n_h = obj.height();
    n_h = Math.min(n_h,max_h);
    obj.css({
      'top' : parseInt( ( winHeight - n_h ) / 2)+'px'
    });
  }

  onStandardClose() {
    ReactDOM.unmountComponentAtNode(document.getElementById('overlay-container'));
  }

  onStandardGo() {
    window.location = '/standards/'+this.state.id;
  }

  doSearch() {
    const api = '/api/standards';
    const sid = this.state.id;
	const tid = this.state.tid;
	
    if(sid) {
      var url = `${api}/${sid}`;
    } else if(tid) {
      var url = `${api}/?tid=${tid}`;
    }

    if(url) {
      showSearching();
      axios.get(url)
      .then(({ data }) => {
        hideSearching();
        this.setState({
          fields: data.fields.standard,
          standard: data.standard,
        });
      });
    }
  }
}

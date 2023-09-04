const { ChartJSNodeCanvas } = require('chartjs-node-canvas');

const width = 400; //px
const height = 400; //px
const backgroundColour = 'white'; // Uses https://www.w3schools.com/tags/canvas_fillstyle.asp

async function drawImage(chart) {
    const w 	= chart.width || width;
	const h 	= chart.height || height;
	const bg 	= chart.backgroundColour || backgroundColour;

	let payload = chart.payload;
	payload.plugins = [{
				id: 'background-colour',
				beforeDraw: (ch) => {
					const ctx = ch.ctx;
					ctx.save();
					ctx.fillStyle = bg;
					ctx.fillRect(0, 0, w, h);
					ctx.restore();
				}
			}]
	
	let chartJSNodeCanvas = new ChartJSNodeCanvas({ width: w, height: h, backgroundColour: bg});
    const image = await chartJSNodeCanvas.renderToBuffer(chart.payload);
    return image.toString('base64');
};


/* Ex.: node index.js '{"type": "bar"}' */
(async () => {
	try {
		let chart = process.argv[2];
		if(!chart) {
			throw new TypeError("Required JSON chart");
		}

		chart = JSON.parse(chart);
		if(!chart.payload || JSON.stringify(chart.payload) === "{}") {
			throw new TypeError("Required JSON chart.payload");
		}
		
		console.log(JSON.stringify({
			status:true,
			image: await drawImage(chart)
		}));
	} catch (ex) {
		console.log(JSON.stringify({
				status: false,
				message: ex.message
			}));
	}
})();